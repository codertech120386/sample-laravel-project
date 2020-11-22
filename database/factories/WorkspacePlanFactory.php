<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\WorkspacePlan;
use Faker\Generator as Faker;

$factory->define(WorkspacePlan::class, function (Faker $faker) {
    $duration = $faker->randomElement([1, 7, 30, 60, 90, 180, 365]);
    if ($duration == 1) $title = "Daily";
    else if ($duration == 7) $title = "Weekly";
    else if ($duration == 30) $title = "Monthly";
    else if ($duration == 90) $title = "Quarterly";
    else if ($duration == 180) $title = "Half Yearly";
    else if ($duration == 365) $title = "Yearly";
    else $title = "NA";

    return [
        "workspace_id" => $faker->numberBetween(1, 100),
        "space_type" => $faker->randomElement(['Open Desk', 'Private Cabin', 'Meeting Room', 'Training Space', 'Semi-Private Cubical', 'Event Space', 'Virtual Office Address']),
        "duration" => $duration,
        "title" => $title,
        "sub_title" => $faker->paragraph(),
        "cost" => $faker->randomElement([300, 400, 500, 600, 700, 800, 900, 1000]),
        "location_type" => $faker->randomElement(['Single Location', 'Multiple Location']),
    ];
});
