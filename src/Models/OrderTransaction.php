<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Rockbuzz\LaraOrders\Events\OrderTransactionCreated;

class OrderTransaction extends Model
{
    use SoftDeletes, Uuid;

    protected $fillable = [
        'payload',
        'order_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'payload' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    protected $dispatchesEvents = [
        'created' => OrderTransactionCreated::class
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
