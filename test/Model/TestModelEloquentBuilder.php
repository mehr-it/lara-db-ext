<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 29.11.18
	 * Time: 15:16
	 */

	namespace MehrItLaraDbExtTest\Model;


	use MehrIt\LaraDbExt\Model\DbExtensions;

	class TestModelEloquentBuilder extends TestModel
	{
		use DbExtensions;

		protected $table = 'test_table';
	}