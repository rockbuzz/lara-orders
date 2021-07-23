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

    public function createOrder(array $notes = []): Order
    {
        return $this->orders()->create($notes);
    }

    public function orderById(int $id): ?Order
    {
        return $this->orderByIdentifier('id', $id);
    }

    public function orderByUuid(string $uuid): ?Order
    {
        return $this->orderByIdentifier('uuid', $uuid);
    }

    protected function orderByIdentifier(string $column, $value): ?Order
    {
        return $this->orders()->where($column, $value)->first();
    }
}
