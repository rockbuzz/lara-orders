<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->smallInteger('status')->default(1);
            $table->json('notes')->nullable();
            $table->morphs('buyer');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['buyer_id', 'buyer_type', 'uuid']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('description');
            $table->smallInteger('amount');
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
    }
}
