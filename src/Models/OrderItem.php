<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Rockbuzz\LaraOrders\Events\OrderItemCreated;

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
        return $this->belongsTo(Order::class);
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
