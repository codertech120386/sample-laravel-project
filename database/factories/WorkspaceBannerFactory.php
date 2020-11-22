<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\WorkspaceBanner;
use Faker\Generator as Faker;

$factory->define(WorkspaceBanner::class, function (Faker $faker) {
    return [
        "workspace_id" => $faker->numberBetween(1, 100),
        "title" => $faker->sentence(),
        "sub_title" => $faker->sentence()
    ];
});
