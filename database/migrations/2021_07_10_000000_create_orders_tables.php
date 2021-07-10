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
            $table->smallInteger('status')->default(1);
            $table->json('metadata')->nullable();
            $table->morphs('buyer');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->smallInteger('amount');
            $table->smallInteger('quantity')->default(1);
            $table->json('metadata')->nullable();
            $table->morphs('buyable');
            $table->foreignId('order_id')->constrained();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
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
