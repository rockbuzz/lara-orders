<?php

namespace Rockbuzz\LaraOrders\Models;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class OrderCoupon extends Model
{
    use SoftDeletes, Uuid;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'value',
        'usage_limit',
        'active',
        'notes',
        'start_at',
        'end_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'active' => 'boolean',
        'notes' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
        'start_at',
        'end_at'
    ];
}
