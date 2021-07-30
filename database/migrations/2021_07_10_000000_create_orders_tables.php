<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Rockbuzz\LaraOrders\Models\OrderCoupon;

class CreateOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_coupons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('name');
            $table->smallInteger('type')->default(OrderCoupon::CURRENCY);
            $table->unsignedInteger('value')->comment('Value must be an integer representing a percentage or cents');
            $table->smallInteger('usage_limit')->nullable();
            $table->boolean('active')->default(true);
            $table->json('notes')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->smallInteger('status')->default(1);
            $table->smallInteger('payment_method')->nullable();
            $table->string('driver')->nullable();
            $table->json('notes')->nullable();
            $table->morphs('buyer');
            $table->foreignId('coupon_id')->nullable()->constrained('order_coupons');
            $table->unsignedInteger('discount_in_cents')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['buyer_id', 'buyer_type', 'uuid']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('description');
            $table->unsignedInteger('amount_in_cents');
            $table->smallInteger('quantity')->default(1);
            $table->json('options')->nullable();
            $table->morphs('buyable');
            $table->foreignId('order_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['buyable_id', 'buyable_type', 'order_id']);
        });

        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->smallInteger('type')->default(1);
            $table->json('payload')->nullable();
            $table->foreignId('order_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_transactions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_coupons');
    }
}
