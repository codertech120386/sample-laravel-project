<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\WorkspaceImage;
use Faker\Generator as Faker;

$factory->define(WorkspaceImage::class, function (Faker $faker) {
    return [
        "image_url" => "https://coffic-images.s3.ap-south-1.amazonaws.com/photo-1554118811-1e0d58224f24.jpg",
        "workspace_id" => $faker->numberBetween(1, 100)
    ];
});
