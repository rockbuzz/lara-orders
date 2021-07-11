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

    protected $schemalessAttributes = [
        'options'
    ];

    public function scopeWithOptionsAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('options');
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
