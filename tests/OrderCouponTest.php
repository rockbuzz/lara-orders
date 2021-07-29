<?php

namespace Tests;

use Rockbuzz\LaraOrders\Traits\Uuid;
use Rockbuzz\LaraOrders\Models\OrderCoupon;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderCouponTest extends TestCase
{
    protected OrderCoupon $orderCoupon;

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
    public function order_coupon_casts()
    {
        $expected = [
            'id' => 'integer',
            'type' => 'integer',
            'active' => 'boolean',
            'notes' => 'array',
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

    /** @test */
    public function coupon_is_currency()
    {
        $coupon = $this->create(OrderCoupon::class, ['type' => OrderCoupon::PERCENTAGE]);

        $this->assertFalse($coupon->isCurrency());

        $coupon->update(['type' => OrderCoupon::CURRENCY]);

        $this->assertTrue($coupon->isCurrency());
    }

    /** @test */
    public function coupon_is_percentage()
    {
        $coupon = $this->create(OrderCoupon::class, ['type' => OrderCoupon::CURRENCY]);

        $this->assertFalse($coupon->isPercentage());

        $coupon->update(['type' => OrderCoupon::PERCENTAGE]);

        $this->assertTrue($coupon->isPercentage());
    }

    /** @test */
    public function coupon_unavaiable_when_inactive()
    {
        $coupon = $this->create(OrderCoupon::class, [
            'start_at' => now()->subMinute(),
            'end_at' => now()->addMinute(),
            'active' => false
        ]);

        $this->assertFalse($coupon->isAvailable());
    }

    /** @test */
    public function coupon_unavaiable_when_out_of_date_use()
    {
        $coupon = $this->create(OrderCoupon::class, [
            'start_at' => now()->addMinute(),
            'end_at' => now()->addMinute(),
            'active' => true
        ]);

        $this->assertFalse($coupon->isAvailable());

        $coupon = $this->create(OrderCoupon::class, [
            'start_at' => now()->subMinute(),
            'end_at' => now()->subMinute(),
            'active' => true
        ]);

        $this->assertFalse($coupon->isAvailable());
    }

    /** @test */
    public function coupon_is_avaiable()
    {
        $coupon = $this->create(OrderCoupon::class, [
            'start_at' => now()->subMinute(),
            'end_at' => now()->addMinute(),
            'active' => true
        ]);

        $this->assertTrue($coupon->isAvailable());
    }
}
