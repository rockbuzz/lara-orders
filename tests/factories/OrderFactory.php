<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Models\User;
use Faker\Generator as Faker;
use Rockbuzz\LaraOrders\Models\Order;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'status' => 1,
        'metadata' => null,
        'buyer_id' => factory(User::class)->create(),
        'buyer_type' => User::class
    ];
});
