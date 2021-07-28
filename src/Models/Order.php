<?php

namespace Rockbuzz\LaraOrders\Models;

use DomainException;
use Rockbuzz\LaraOrders\Schemaless\Notes;
use Rockbuzz\LaraOrders\Traits\Uuid;
use Rockbuzz\LaraOrders\Events\OrderCreated;
use Rockbuzz\LaraOrders\Events\CouponApplied;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Rockbuzz\LaraUtils\Casts\Schemaless;

class Order extends Model
{
    use SoftDeletes, Uuid;

    protected $fillable = [
        'uuid',
        'status',
        'payment_method',
        'driver',
        'notes',
        'buyer_id',
        'buyer_type'
    ];

    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'discount_in_cents' => 'integer',
        'notes' => Schemaless::class . ':' . Notes::class
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
        'paid_at'
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
        $this->isValidOrFail($coupon)
            ->saveDiscount($coupon);

        event(new CouponApplied($this, $coupon));
    }

    public function getTotalAttribute()
    {
        return format_currency($this->totalInCents);
    }

    public function getTotalInCentsAttribute()
    {
        return $this->items->reduce(fn($acc, $item) => $acc += $item->totalInCents);
    }

    public function getTotalWithDiscountAttribute()
    {
        return format_currency($this->totalWithDiscountInCents);
    }

    public function getTotalWithDiscountInCentsAttribute()
    {
        return $this->totalInCents - $this->discount_in_cents;
    }

    public function isWorthless(): bool
    {
        return $this->totalInCents <= 0;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class);
    }

    public function addTransaction(array $payload, int $type)
    {
        return $this->transactions()->create([
            'type' => $type,
            'payload' => $payload
        ]);
    }

    protected function isValidOrFail(OrderCoupon $coupon): self
    {
        throw_unless(
            $this->couponHasAvailableLimit($coupon),
            new DomainException('Coupon exceeded usage limit')
        );

        throw_if(
            $this->items->isEmpty(),
            new DomainException('Order is empty')
        );

        throw_if(
            $this->items->isNotEmpty() && $this->total <= 0,
            new DomainException('Order total is zero')
        );

        throw_if(
            $this->discountIsGreaterThanTotal($coupon),
            new DomainException('Discount is greater than total')
        );

        return $this;
    }

    protected function couponHasAvailableLimit(OrderCoupon $coupon)
    {
        return $coupon->isUnlimited()
            or $coupon->usage_limit > static::where('coupon_id', $coupon->id)->count('id');
    }

    protected function discountIsGreaterThanTotal($coupon)
    {
        return $coupon->resolveDiscount($this->total) > $this->total;
    }

    private function saveDiscount(OrderCoupon $coupon)
    {
        $this->discount_in_cents = to_pennies($coupon->resolveDiscount($this->total));
        $this->coupon_id = $coupon->id;
        $this->save();
    }
}
