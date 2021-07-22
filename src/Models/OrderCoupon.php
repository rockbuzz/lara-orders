<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class OrderCoupon extends Model
{
    use SoftDeletes, Uuid;

    const CURRENCY = 1;
    const PERCENTAGE = 2;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'value',
        'usage_limit',
        'active',
        'notes',
        'start_at',
        'end_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'type' => 'integer',
        'active' => 'boolean',
        'notes' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
        'start_at',
        'end_at'
    ];

    public function isUnlimited(): bool
    {
        return is_null($this->usage_limit);
    }

    public function isCurrency(): bool
    {
        return $this->type === static::CURRENCY;
    }

    public function isPercentage(): bool
    {
        return $this->type === static::PERCENTAGE;
    }

    public function isAvailable(): bool
    {
        return $this->active and $this->start_at->lte(now()) and $this->end_at->gte(now());
    }

    public function resolveDiscount(float $float): float
    {
        if ($this->isPercentage()) {
            return percentage_of($this->value, $float);
        }
        return $this->value / 100;
    }
}
