<?php

namespace Rockbuzz\LaraOrders\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Rockbuzz\LaraOrders\Events\OrderTransactionCreated;
use Rockbuzz\LaraOrders\Traits\HasSchemalessAttributes;
use Spatie\SchemalessAttributes\{SchemalessAttributes, SchemalessAttributesTrait};

class OrderTransaction extends Model
{
    use SoftDeletes, SchemalessAttributesTrait, HasSchemalessAttributes;

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

    protected $schemalessAttributes = [
        'payload'
    ];

    protected $dispatchesEvents = [
        'created' => OrderTransactionCreated::class
    ];

    public function scopeWithPayloadAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('payload');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
