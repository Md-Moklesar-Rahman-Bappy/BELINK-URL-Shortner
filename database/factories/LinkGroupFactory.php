<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Link;
use App\LinkGroup;
use Faker\Generator as Faker;

$factory->define(LinkGroup::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'hash' => Str::random(10),
        'user_id' => $faker->numberBetween(1, 100),
        'public' => true,
        'rotator' => false,
    ];
});
