<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 06.12.18
	 * Time: 19:29
	 */

	namespace MehrItLaraDbExtTest\Model;


	use MehrIt\LaraDbExt\Model\DbExtensions;

	class TestModelEloquentBuilderHasOneChild extends TestModel
	{
		use DbExtensions;

		protected $table = 'test_eloquent_has_one_child_table';
	}