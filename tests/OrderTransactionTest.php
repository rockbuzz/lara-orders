<?php

namespace Tests;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rockbuzz\LaraOrders\Models\{Order, OrderTransaction};

class OrderTransactionTest extends TestCase
{
    protected $orderTransaction;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderTransaction = new OrderTransaction();
    }

    /** @test */
    public function order_item_traits()
    {
        $expected = [
            SoftDeletes::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(OrderTransaction::class))
        );
    }

    /** @test */
    public function order_transaction_fillable()
    {
        $expected = [
            'payload',
            'order_id'
        ];

        $this->assertEquals($expected, $this->orderTransaction->getFillable());
    }

    /** @test */
    public function order_item_casts()
    {
        $expected = [
            'id' => 'integer',
            'payload' => 'array',
            'deleted_at' => 'datetime'
        ];

        $this->assertEquals($expected, $this->orderTransaction->getCasts());
    }

    /** @test */
    public function dates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->orderTransaction->getDates())
        );
    }

    /** @test */
    public function order_transaction_has_order()
    {
        $order = $this->create(Order::class);
        $orderTransaction = $this->create(OrderTransaction::class, [
            'order_id' => $order->id
        ]);

        $this->assertInstanceOf(BelongsTo::class, $orderTransaction->order());
        $this->assertEquals($order->id, $orderTransaction->order->id);
    }
}
