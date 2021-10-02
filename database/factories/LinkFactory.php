<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Link;
use Faker\Generator as Faker;

$factory->define(Link::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'long_url' => $faker->url,
        'hash' => Str::random(10),
        'user_id' => $faker->numberBetween(1, 100),
        'description' => $faker->sentence(10),
        'type' => Arr::random(['frame', 'direct', 'overlay', 'splash']),
    ];
});
