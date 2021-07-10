<?php

namespace Rockbuzz\LaraOrders\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Rockbuzz\LaraOrders\Traits\HasSchemalessAttributes;
use Spatie\SchemalessAttributes\{SchemalessAttributes, SchemalessAttributesTrait};

class OrderItem extends Model
{
    use SoftDeletes, SchemalessAttributesTrait, HasSchemalessAttributes;

    protected $fillable = [
        'description',
        'amount',
        'quantity',
        'metadata',
        'buyable_id',
        'buyable_type',
        'order_id'
    ];

    protected $casts = [
        'id' => 'integer',
        'amount' => 'integer',
        'quantity' => 'integer',
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

    public function scopeWithMetadataAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('metadata');
    }

    public function buyable()
    {
        return $this->morphTo('buyable');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
