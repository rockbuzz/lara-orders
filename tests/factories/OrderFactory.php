<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Models\User;
use Faker\Generator as Faker;
use Ramsey\Uuid\Uuid;
use Rockbuzz\LaraOrders\Models\Order;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'uuid' => Uuid::uuid4()->toString(),
        'status' => 1,
        'notes' => null,
        'buyer_id' => factory(User::class)->create(),
        'buyer_type' => User::class
    ];
});
