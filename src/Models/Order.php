<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Rockbuzz\LaraOrders\Events\OrderCreated;
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(OrderCoupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTotalAttribute()
    {
        return $this->items->reduce(fn($acc, $item) => $acc += $item->total);
    }

    public function getTotalWithCouponAttribute()
    {
        if (!$this->coupon) return $this->total;

        $total = $this->total / 100;

        return $total - $this->resolveDiscount($total);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class);
    }

    protected function resolveDiscount($total)
    {
        if ($this->coupon->isPercentage()) {
            return ($this->coupon->value / 100) * $total;
        }

        return $this->coupon->value / 100;
    }
}
