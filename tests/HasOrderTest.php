<?php

namespace Tests;

use Ramsey\Uuid\Uuid;
use Tests\Models\User;
use Rockbuzz\LaraOrders\Models\Order;
use Illuminate\Support\Facades\Event;
use Rockbuzz\LaraOrders\Events\OrderCreated;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class HasOrderTest extends TestCase
{
    protected $orderItem;

    /** @test */
    public function buyer_can_have_orders()
    {
        $buyer = $this->create(User::class);
        $order = $this->create(Order::class, [
            'buyer_id' => $buyer->id,
            'buyer_type' => User::class
        ]);

        $this->assertInstanceOf(MorphMany::class, $buyer->orders());
        $this->assertContains($order->id, $buyer->orders->pluck('id'));
    }

    /** @test */
    public function buyer_can_create_order()
    {
        Event::fake(OrderCreated::class);

        $buyer = $this->create(User::class);

        $order = $buyer->createOrder();

        $this->assertInstanceOf(Order::class, $order);

        $this->assertDatabaseHas('orders', [
            'status' => 1,
            'buyer_id' => $buyer->id,
            'buyer_type' => User::class
        ]);

        Event::assertDispatched(OrderCreated::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });
    }

    /** @test */
    public function must_throw_an_exception_when_order_already_exists()
    {
        $order = $this->create(Order::class);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->create(Order::class, [
            'uuid' => $order->uuid,
            'buyer_id' => $order->buyer_id,
            'buyer_type' => $order->buyer_type
        ]);
    }

    /** @test */
    public function buyer_can_find_order_by_id()
    {
        $buyer = $this->create(User::class);

        $this->assertNull($buyer->orderById(999));

        $order = $this->create(Order::class, [
            'buyer_id' => $buyer->id,
            'buyer_type' => User::class
        ]);

        $this->assertEquals($order->id, $buyer->orderById($order->id)->id);
    }

    /** @test */
    public function buyer_can_find_order_by_uuid()
    {
        $buyer = $this->create(User::class);

        $this->assertNull($buyer->orderByUuid(Uuid::uuid4()->toString()));

        $order = $this->create(Order::class, [
            'buyer_id' => $buyer->id,
            'buyer_type' => User::class
        ]);

        $this->assertEquals($order->id, $buyer->orderByUuid($order->uuid)->id);
    }
}
