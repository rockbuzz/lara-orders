<?php

namespace Rockbuzz\LaraOrders\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Rockbuzz\LaraOrders\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, Relations\HasMany, SoftDeletes};

/**
 * @property integer $id
 * @property string $uuid
 * @property string $name,
 * @property integer $type
 * @property integer $value
 * @property integer|null $usage_limit
 * @property boolean $active
 * @property array|null $notes
 * @property Collection $orders
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class OrderCoupon extends Model
{
    use SoftDeletes, Uuid;

    public const CURRENCY = 1;
    public const PERCENTAGE = 2;

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
        'notes' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
        'start_at',
        'end_at'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(config('orders.models.order'));
    }

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
