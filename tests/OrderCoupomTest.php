<?php

namespace Tests;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Rockbuzz\LaraOrders\Models\OrderCoupon;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderCoupomTest extends TestCase
{
    protected $orderCoupon;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderCoupon = new OrderCoupon();
    }

    /** @test */
    public function order_item_traits()
    {
        $expected = [
            SoftDeletes::class,
            Uuid::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(OrderCoupon::class))
        );
    }

    /** @test */
    public function order_transaction_fillable()
    {
        $expected = [
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

        $this->assertEquals($expected, $this->orderCoupon->getFillable());
    }

    /** @test */
    public function order_item_casts()
    {
        $expected = [
            'id' => 'integer',
            'active' => 'boolean',
            'notes' => 'array',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];

        $this->assertEquals($expected, $this->orderCoupon->getCasts());
    }

    /** @test */
    public function dates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at', 'start_at', 'end_at']),
            array_values($this->orderCoupon->getDates())
        );
    }
}
