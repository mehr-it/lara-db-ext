<?php


	use Faker\Generator as Faker;

	/** @var \Illuminate\Database\Eloquent\Factory $factory */
	$factory->define(\MehrItLaraDbExtTest\Model\Post::class, function (Faker $faker) {
		return [
			'user_id' => function() {
				return factory(\MehrItLaraDbExtTest\Model\User::class)->create()->id;
			}
		];
	});