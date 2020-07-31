<?php

	use Faker\Generator as Faker;

	/** @var \Illuminate\Database\Eloquent\Factory $factory */
	$factory->define(\MehrItLaraDbExtTest\Model\TestModelEloquentBuilderHasOneChild::class, function (Faker $faker) {
		return [
			'name'    => $faker->name,
			'x'       => $faker->randomNumber(6),
			'root_id' => function () {
				return factory(\MehrItLaraDbExtTest\Model\TestModelEloquentBuilderHasOneRoot::class)->create()->id;
			}
		];
	});