<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Amenity;
use Faker\Generator as Faker;

$factory->define(Amenity::class, function (Faker $faker) {
    return [
        "name" => $faker->sentence($nbWords = 2),
        "icon_url" => $faker->url
    ];
});
