<?php

namespace Tests;

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
        Event::fake();

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
    public function buyer_can_find_order_by_id()
    {
        $buyer = $this->create(User::class);

        $this->assertNull($buyer->findOrderById('xx'));

        $order = $this->create(Order::class, [
            'buyer_id' => $buyer->id,
            'buyer_type' => User::class
        ]);

        $this->assertEquals($order->id, $buyer->findOrderById($order->id)->id);
    }
}
