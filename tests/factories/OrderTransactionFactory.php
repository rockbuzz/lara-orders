<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Rockbuzz\LaraOrders\Models\{Order, OrderTransaction};

$factory->define(OrderTransaction::class, function (Faker $faker) {
    return [
        'payload' => null,
        'order_id' => factory(Order::class)->create()
    ];
});
