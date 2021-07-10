<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Tests\Models\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(2)
    ];
});
