<?php

declare(strict_types=1);

namespace Rockbuzz\LaraOrders\Traits;

use Rockbuzz\LaraOrders\Models\Order;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasOrder
{
    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'buyer');
    }

    public function createOrder(?array $metadata = null): Order
    {
        return $this->orders()->create([
            'metadata' => $metadata
        ]);
    }

    public function findOrderById($id): ?Order
    {
        return $this->orders()->whereId($id)->first();
    }
}
