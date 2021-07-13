<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Ramsey\Uuid\Uuid;
use Faker\Generator as Faker;
use Rockbuzz\LaraOrders\Models\OrderCoupon;

$factory->define(OrderCoupon::class, function (Faker $faker) {
    return [
        'uuid' => Uuid::uuid4(),
        'name' => $faker->word(),
        'type' => $faker->numberBetween(1, 2),
        'value' => $faker->numberBetween(1000, 10000),
        'usage_limit' => 1,
        'active' => $faker->boolean(),
        'notes' => null,
        'start_at' => now(),
        'end_at' => now()->addDay()
    ];
});
