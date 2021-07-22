<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Models\Product;
use Faker\Generator as Faker;
use Rockbuzz\LaraOrders\Models\{Order, OrderItem};

$factory->define(OrderItem::class, function (Faker $faker) {
    $product = factory(Product::class)->create();
    return [
        'description' => $faker->sentence,
        'amount_in_cents' => $faker->numberBetween(1990, 3999),
        'quantity' => $faker->numberBetween(1, 3),
        'options' => null,
        'buyable_id' => $product->id,
        'buyable_type' => Product::class,
        'order_id' => factory(Order::class)->create()
    ];
});
