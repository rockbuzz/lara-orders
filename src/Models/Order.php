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

    public function scopeWithNotesAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('notes');
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
