<?php

namespace Tests;

use Tests\Models\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rockbuzz\LaraOrders\Models\{Order, OrderItem};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

class OrderItemTest extends TestCase
{
    protected $orderItem;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderItem = new OrderItem();
    }

    /** @test */
    public function order_item_traits()
    {
        $expected = [
            SoftDeletes::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(OrderItem::class))
        );
    }

    /** @test */
    public function order_item_fillable()
    {
        $expected = [
            'description',
            'amount',
            'quantity',
            'options',
            'buyable_id',
            'buyable_type',
            'order_id'
        ];

        $this->assertEquals($expected, $this->orderItem->getFillable());
    }

    /** @test */
    public function order_item_casts()
    {
        $expected = [
            'id' => 'integer',
            'amount' => 'integer',
            'quantity' => 'integer',
            'options' => 'array',
            'deleted_at' => 'datetime'
        ];

        $this->assertEquals($expected, $this->orderItem->getCasts());
    }

    /** @test */
    public function dates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->orderItem->getDates())
        );
    }

    /** @test */
    public function order_item_has_product()
    {
        $buyable = $this->create(Product::class);
        $orderItem = $this->create(OrderItem::class, [
            'buyable_id' => $buyable->id,
            'buyable_type' => Product::class
        ]);

        $this->assertInstanceOf(MorphTo::class, $orderItem->buyable());
        $this->assertEquals($buyable->id, $orderItem->buyable->id);
    }

    /** @test */
    public function order_item_has_order()
    {
        $order = $this->create(Order::class);
        $item = $this->create(OrderItem::class, [
            'order_id' => $order->id
        ]);

        $this->assertInstanceOf(BelongsTo::class, $item->order());
        $this->assertEquals($order->id, $item->order->id);
    }

    /** @test */
    public function must_throw_an_exception_when_item_already_exists()
    {
        $order = $this->create(Order::class);
        $item = $this->create(OrderItem::class, [
            'order_id' => $order->id
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->create(OrderItem::class, [
            'order_id' => $order->id,
            'buyable_id' => $item->buyable_id,
            'buyable_type' => $item->buyable_type
        ]);
    }
}
