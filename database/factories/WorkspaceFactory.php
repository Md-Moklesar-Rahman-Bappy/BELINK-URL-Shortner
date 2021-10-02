<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Workspace;
use Faker\Generator as Faker;

$factory->define(Workspace::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'owner_id' => $faker->numberBetween(1, 100),
    ];
});
