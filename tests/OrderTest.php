<?php

namespace Tests;

use DomainException;
use Tests\Models\User;
use Rockbuzz\LaraOrders\Traits\Uuid;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Rockbuzz\LaraUtils\Casts\Schemaless;
use Rockbuzz\LaraOrders\Schemaless\Notes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Rockbuzz\LaraOrders\Events\{CouponApplied, OrderTransactionCreated};
use Rockbuzz\LaraOrders\Models\{OrderItem, Order, OrderCoupon, OrderTransaction};

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
            Uuid::class
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
            'uuid',
            'status',
            'payment_method',
            'driver',
            'notes',
            'buyer_id',
            'buyer_type',
            'due_date'
        ];

        $this->assertEquals($expected, $this->order->getFillable());
    }

    /** @test */
    public function order_casts()
    {
        $expected = [
            'id' => 'integer',
            'status' => 'integer',
            'discount_in_cents' => 'integer',
            'notes' => Schemaless::class . ':' . Notes::class,
            'deleted_at' => 'datetime'
        ];

        $this->assertEquals($expected, $this->order->getCasts());
    }

    /** @test */
    public function order_dates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at', 'paid_at', 'due_date']),
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
    public function order_has_is_worthless()
    {
        $order = $this->create(Order::class);
        $item = $this->create(OrderItem::class, [
            'order_id' => $order->id
        ]);

        $this->assertFalse($order->isWorthless());

        $item->update(['amount_in_cents' => 0]);

        $order->refresh();

        $this->assertTrue($order->isWorthless());
    }

    /** @test */
    public function order_can_have_coupon()
    {
        $coupon = $this->create(OrderCoupon::class);
        $order = $this->create(Order::class, [
            'coupon_id' => $coupon->id
        ]);

        $this->assertInstanceOf(BelongsTo::class, $order->coupon());
        $this->assertEquals($coupon->id, $order->coupon->id);
    }

    /** @test */
    public function order_has_apply_currency_coupon()
    {
        Event::fake(CouponApplied::class);

        $coupon = $this->create(
            OrderCoupon::class,
            [
                'start_at' => now()->subMinute(),
                'end_at' => now()->addMinute(),
                'active' => true,
                'usage_limit' => null,
                'type' => OrderCoupon::CURRENCY,
                'value' => 1000
            ]
        );
        $order = $this->create(Order::class);
        $this->create(OrderItem::class, ['order_id' => $order->id]);

        $order->applyCoupon($coupon);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'coupon_id' => $coupon->id,
            'discount_in_cents' => 1000
        ]);

        Event::assertDispatched(CouponApplied::class, function ($e) use ($order, $coupon) {
            return $e->order->id === $order->id and $e->coupon->id === $coupon->id;
        });
    }

    /** @test */
    public function must_return_an_exception_when_coupon_exceeded_usage_limit()
    {
        $coupon = $this->create(
            OrderCoupon::class,
            [
                'start_at' => now()->subMinute(),
                'end_at' => now()->addMinute(),
                'active' => true,
                'usage_limit' => 1,
                'type' => OrderCoupon::CURRENCY,
                'value' => 1000
            ]
        );
        $this->create(Order::class, ['coupon_id' => $coupon->id]);

        $order = $this->create(Order::class);
        $this->create(OrderItem::class, ['order_id' => $order->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Coupon exceeded usage limit');

        $order->applyCoupon($coupon);
    }

    /** @test */
    public function must_return_an_exception_when_order_is_empty()
    {
        $coupon = $this->create(
            OrderCoupon::class,
            [
                'start_at' => now()->subMinute(),
                'end_at' => now()->addMinute(),
                'active' => true,
                'usage_limit' => null,
                'type' => OrderCoupon::CURRENCY,
                'value' => 1000
            ]
        );
        $order = $this->create(Order::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Order is empty');

        $order->applyCoupon($coupon);
    }

    /** @test */
    public function must_return_an_exception_when_order_total_is_zero()
    {
        $coupon = $this->create(
            OrderCoupon::class,
            [
                'start_at' => now()->subMinute(),
                'end_at' => now()->addMinute(),
                'active' => true,
                'usage_limit' => null,
                'type' => OrderCoupon::CURRENCY,
                'value' => 1000
            ]
        );
        $order = $this->create(Order::class);
        $this->create(OrderItem::class, [
            'order_id' => $order->id,
            'amount_in_cents' => 0
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Order total is zero');

        $order->applyCoupon($coupon);
    }

    /** @test */
    public function must_return_an_exception_when_discount_is_greater_than_total()
    {
        $coupon = $this->create(
            OrderCoupon::class,
            [
                'start_at' => now()->subMinute(),
                'end_at' => now()->addMinute(),
                'active' => true,
                'usage_limit' => 1,
                'type' => OrderCoupon::CURRENCY,
                'value' => 10100
            ]
        );
        $order = $this->create(Order::class);
        $this->create(OrderItem::class, [
            'order_id' => $order->id,
            'amount_in_cents' => 10000,
            'quantity' => 1
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Discount is greater than total');

        $order->applyCoupon($coupon);
    }

    /** @test */
    public function order_has_total_without_discount()
    {
        $order = $this->create(Order::class);
        [$item1, $item2] = $this->create(OrderItem::class, [
            'order_id' => $order->id
        ], 2);

        $expected = $item1->total + $item2->total;

        $this->assertEquals($expected, $order->total);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'coupon_id' => null,
            'discount_in_cents' => null
        ]);
    }

    /** @test */
    public function order_has_total_with_discount_when_currency_coupon()
    {
        $coupon = $this->create(OrderCoupon::class, [
            'start_at' => now()->subMinute(),
            'end_at' => now()->addMinute(),
            'active' => true,
            'usage_limit' => null,
            'type' => 1,
            'value' => 1000
        ]);

        $order = $this->create(Order::class, [
            'coupon_id' => $coupon->id,
            'discount_in_cents' => 1000
        ]);

        $this->create(OrderItem::class, [
            'order_id' => $order->id,
            'amount_in_cents' => 9899,
            'quantity' => 1
        ], 2);

        $order->applyCoupon($coupon);

        $expected = $order->totalInCents - $order->discount_in_cents;

        $this->assertEquals($expected, $order->totalWithDiscountInCents);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'coupon_id' => $coupon->id,
            'discount_in_cents' => $order->discount_in_cents
        ]);
    }

    /** @test */
    public function order_has_total_with_discount_when_percentage_coupon()
    {
        $coupon = $this->create(OrderCoupon::class, [
            'start_at' => now()->subMinute(),
            'end_at' => now()->addMinute(),
            'active' => true,
            'usage_limit' => null,
            'type' => OrderCoupon::PERCENTAGE,
            'value' => 10
        ]);

        $order = $this->create(Order::class);
        $this->create(OrderItem::class, [
            'order_id' => $order->id,
            'amount_in_cents' => 9899,
            'quantity' => 1
        ], 2);

        $order->applyCoupon($coupon);

        $expected = $order->totalInCents - $order->discount_in_cents;

        $this->assertEquals($expected, $order->totalWithDiscountInCents);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'coupon_id' => $coupon->id,
            'discount_in_cents' => $order->discount_in_cents
        ]);
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

    /** @test */
    public function order_can_have_add_transaction()
    {
        Event::fake(OrderTransactionCreated::class);

        $order = $this->create(Order::class);

        $payload = ['any' => 1];
        $type = 1;

        $transaction = $order->addTransaction($payload, $type);

        $this->assertInstanceOf(OrderTransaction::class, $transaction);

        $this->assertDatabaseHas('order_transactions', [
            'order_id' => $order->id,
            'id' => $transaction->id,
            'type' => $type
        ]);

        Event::assertDispatched(OrderTransactionCreated::class, function ($e) use ($transaction) {
            return $e->transaction->id === $transaction->id;
        });
    }

    /** @test */
    public function order_can_need_a_payment_method()
    {
        $order = $this->create(Order::class, [
            'payment_method' => 'any'
        ]);

        $this->assertFalse($order->needAPaymentMethod());

        $order->update(['payment_method' => null]);

        $this->assertFalse($order->needAPaymentMethod());

        $order->items()->create([
            'description' => 'Any Desc',
            'amount_in_cents' => 100,
            'buyable_id' => 1,
            'buyable_type' => Event::class
        ]);

        $order->refresh();

        $this->assertTrue($order->needAPaymentMethod());
    }
}
