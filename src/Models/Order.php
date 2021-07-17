<?php

namespace Rockbuzz\LaraOrders\Models;

use DomainException;
use Rockbuzz\LaraOrders\Traits\Uuid;
use Rockbuzz\LaraOrders\Events\OrderCreated;
use Rockbuzz\LaraOrders\Events\CouponApplied;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use SoftDeletes, Uuid;

    protected $fillable = [
        'uuid',
        'status',
        'notes',
        'buyer_id',
        'buyer_type'
    ];

    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'discount' => 'integer',
        'notes' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    protected $dispatchesEvents = [
        'created' => OrderCreated::class
    ];
    
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(config('orders.models.buyer'));
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(OrderCoupon::class);
    }

    public function applyCoupon(OrderCoupon $coupon)
    {
        $this->isValidOrFail($coupon);

        $this->coupon_id = $coupon->id;

        $discount = $this->convertToCents($this->calculateDiscount($this->total));

        if ($this->totalInCents < $discount) {
            throw new DomainException('Discount is greater than total');
        }
        
        $this->discount = $discount;
        $this->save();

        event(new CouponApplied($this, $coupon));
    }

    public function getTotalAttribute()
    {
        return number_format($this->totalInCents / 100, 2, '.', '');
    }

    public function getTotalInCentsAttribute()
    {
        return $this->items->reduce(fn($acc, $item) => $acc += $item->totalInCents);
    }

    public function getTotalWithDiscountAttribute()
    {
        return $this->totalInCents - $this->discount;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class);
    }

    protected function calculateDiscount()
    {
        if ($this->coupon->isPercentage()) {
            return percentage_of($this->coupon->value, $this->total);
        }

        return $this->coupon->value / 100;
    }

    protected function convertToCents($value)
    {
        return intval($value * 100);
    }

    protected function isValidOrFail(OrderCoupon $coupon)
    {
        throw_unless(
            $this->couponHasAvailableLimit($coupon), 
            new DomainException('Coupon exceeded usage limit')
        );

        throw_if(
            $this->items->isEmpty(), 
            new DomainException('Order is empty')
        );
    }

    protected function discountIsLessThanTotal()
    {
        # code...
    }

    private function couponHasAvailableLimit(OrderCoupon $coupon)
    {
        return is_null($coupon->usage_limit)
            or $coupon->usage_limit > static::where('coupon_id', $coupon->id)->count('id');
    }
}
