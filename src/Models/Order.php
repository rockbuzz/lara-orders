<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Events\OrderCreated;
use Rockbuzz\LaraOrders\Traits\HasSchemalessAttributes;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Spatie\SchemalessAttributes\{SchemalessAttributes, SchemalessAttributesTrait};

class Order extends Model
{
    use SoftDeletes, SchemalessAttributesTrait, HasSchemalessAttributes;

    protected $fillable = [
        'status',
        'metadata',
        'buyer_id',
        'buyer_type'
    ];

    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'metadata' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    protected $schemalessAttributes = [
        'metadata'
    ];

    protected $dispatchesEvents = [
        'created' => OrderCreated::class
    ];

    public function scopeWithMetadataAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('metadata');
    }
    
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
