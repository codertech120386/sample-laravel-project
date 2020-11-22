<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Payment;
use Faker\Generator as Faker;

$factory->define(Payment::class, function (Faker $faker) {
    $amount = $faker->numberBetween(1, 10000000);
    $gst = $amount * 0.18;

    return [
        'user_id' => $faker->numberBetween(1, 100),
        'workspace_plan_id' => $faker->numberBetween(1, 9),
        'amount' => $amount,
        'gst' => $gst,
        'number_of_seats' => $faker->numberBetween(1, 10),
        'transaction_id' => $faker->regexify('[A-Za-z0-9]{15}'),
        'gateway' => $faker->randomElement(['razorpay', 'paytm']),
        'status' => $faker->randomElement(['created', 'authorized', 'captured', 'refunded', 'failed'])
    ];
});
