<?php

namespace Rockbuzz\LaraOrders\Models;

use Carbon\Carbon;
use Rockbuzz\LaraOrders\Traits\Uuid;
use Rockbuzz\LaraOrders\Events\OrderItemCreated;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

/**
 * @property integer $id
 * @property string $uuid
 * @property string $description,
 * @property integer $amount_in_cents
 * @property integer $quantity
 * @property array|null $options
 * @property integer $buyable_id
 * @property string $buyable_type
 * @property integer $order_id
 * @property Order $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class OrderItem extends Model
{
    use SoftDeletes, Uuid;

    protected $fillable = [
        'description',
        'amount_in_cents',
        'quantity',
        'options',
        'buyable_id',
        'buyable_type',
        'order_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'amount_in_cents' => 'integer',
        'quantity' => 'integer',
        'options' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    protected $dispatchesEvents = [
        'created' => OrderItemCreated::class
    ];

    public function buyable()
    {
        return $this->morphTo('buyable');
    }

    public function order()
    {
        return $this->belongsTo(config('orders.models.order'));
    }

    public function getTotalAttribute()
    {
        return format_currency($this->totalInCents);
    }

    public function getTotalInCentsAttribute()
    {
        return $this->attributes['amount_in_cents'] * $this->attributes['quantity'];
    }
}
