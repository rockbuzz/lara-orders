<?php

namespace Rockbuzz\LaraOrders\Traits;

use Ramsey\Uuid\Uuid as RamseyUuid;

trait Uuid
{
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid ??= RamseyUuid::uuid4();
        });
    }
}
