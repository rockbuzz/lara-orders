<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Events\OrderCreated;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Order extends Model
{
    use SoftDeletes;

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

    protected $schemalessAttributes = [
        'notes'
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

    public function transactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class);
    }
}
