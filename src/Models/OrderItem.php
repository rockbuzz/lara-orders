<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class OrderItem extends Model
{
    use SoftDeletes, Uuid;

    protected $fillable = [
        'description',
        'amount',
        'quantity',
        'options',
        'buyable_id',
        'buyable_type',
        'order_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'amount' => 'integer',
        'quantity' => 'integer',
        'options' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public function buyable()
    {
        return $this->morphTo('buyable');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
