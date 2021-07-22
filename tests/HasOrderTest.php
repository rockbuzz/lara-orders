<?php

namespace Tests;

use Ramsey\Uuid\Uuid;
use Tests\Models\User;
use Illuminate\Support\Facades\Event;
use Rockbuzz\LaraOrders\Models\{Order, OrderItem};
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rockbuzz\LaraOrders\Events\{OrderCreated, OrderItemCreated};

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
    public function buyer_can_create_order_with_item()
    {
        Event::fake([OrderCreated::class, OrderItemCreated::class]);

        $buyer = $this->create(User::class);

        $data = $this->make(OrderItem::class);

        $order = $buyer->createOrder();
        $item = $order->items()->create([
            'description' => $data->description,
            'amount_in_cents' => $data->amount_in_cents,
            'quantity' => $data->quantity,
            'buyable_id' => $data->buyable_id,
            'buyable_type' => $data->buyable_type,
            'options' => $data->options,
        ]);

        $this->assertDatabaseHas('orders', [
            'status' => 1,
            'buyer_id' => $buyer->id,
            'buyer_type' => User::class
        ]);

        $this->assertDatabaseHas('order_items', [
            'description' => $data->description,
            'amount_in_cents' => $data->amount_in_cents,
            'quantity' => $data->quantity,
            'buyable_id' => $data->buyable_id,
            'buyable_type' => $data->buyable_type,
            'options' => $data->options,
            'order_id' => $order->id
        ]);

        Event::assertDispatched(OrderCreated::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });

        Event::assertDispatched(OrderItemCreated::class, function ($e) use ($item) {
            return $e->item->id === $item->id;
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
