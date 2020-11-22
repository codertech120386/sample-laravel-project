<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Subscription;
use Faker\Generator as Faker;

$factory->define(Subscription::class, function (Faker $faker) {
    return [
        'payment_id' => $faker->numberBetween(1, 10),
        'start_date' => $faker->date($format = 'Y-m-d'),
        'end_date' => $faker->date($format = 'Y-m-d'),
        'status' => $faker->randomElement(['request sent to workspace', 'awaiting confirmation from workspace', 'workspace confirmed', 'active', 'inactive'])
    ];
});
