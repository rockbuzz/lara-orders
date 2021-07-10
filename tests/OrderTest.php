<?php

namespace Tests;

use Tests\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rockbuzz\LaraOrders\Traits\HasSchemalessAttributes;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Rockbuzz\LaraOrders\Models\{OrderItem, Order, OrderTransaction};

class OrderTest extends TestCase
{
    protected $order;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
    }

    /** @test */
    public function order_traits()
    {
        $expected = [
            SoftDeletes::class,
            SchemalessAttributesTrait::class,
            HasSchemalessAttributes::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(Order::class))
        );
    }

    /** @test */
    public function order_fillable()
    {
        $expected = [
            'status',
            'metadata',
            'buyer_id',
            'buyer_type'
        ];

        $this->assertEquals($expected, $this->order->getFillable());
    }

    /** @test */
    public function order_casts()
    {
        $expected = [
            'id' => 'integer',
            'status' => 'integer',
            'metadata' => 'array',
            'deleted_at' => 'datetime'
        ];

        $this->assertEquals($expected, $this->order->getCasts());
    }

    /** @test */
    public function order_dates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->order->getDates())
        );
    }
    
    /** @test */
    public function order_has_buyer()
    {
        Config::set('orders.models.buyer', User::class);
        $buyer = $this->create(User::class);
        $order = $this->create(Order::class, [
            'buyer_id' => $buyer->id
        ]);

        $this->assertInstanceOf(BelongsTo::class, $order->buyer());
        $this->assertEquals($buyer->id, $order->buyer->id);
    }

    /** @test */
    public function order_has_items()
    {
        $order = $this->create(Order::class);
        $item = $this->create(OrderItem::class, [
            'order_id' => $order->id
        ]);

        $this->assertInstanceOf(HasMany::class, $order->items());
        $this->assertContains($item->id, $order->items->pluck('id'));
    }

    /** @test */
    public function order_can_have_transactions()
    {
        $order = $this->create(Order::class);
        $transaction = $this->create(OrderTransaction::class, [
            'order_id' => $order->id
        ]);

        $this->assertInstanceOf(HasMany::class, $order->transactions());
        $this->assertContains($transaction->id, $order->transactions->pluck('id'));
    }
}
