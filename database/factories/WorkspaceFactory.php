<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Workspace;
use Faker\Generator as Faker;

$factory->define(Workspace::class, function (Faker $faker) {
    return [
        "name" => $faker->sentence($nbWords = 3),
        "description" => $faker->paragraph(),
        "address" => $faker->paragraph(),
        "location_id" => $faker->word(),
        "active" => $faker->boolean(),
        "profile_image" => "https://coffic-images.s3.ap-south-1.amazonaws.com/photo-1554118811-1e0d58224f24.jpg",
        "workspace_type_id" => $faker->numberBetween(1, 4),
    ];
});
