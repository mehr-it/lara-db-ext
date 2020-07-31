<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 29.11.18
	 * Time: 15:17
	 */

	namespace MehrItLaraDbExtTest\Model;


	use MehrIt\LaraDbExt\Model\DbExtensions;

	class TestModelEloquentBuilderBelongs extends TestModel
	{
		use DbExtensions;

		protected $table = 'belongs_table';

		public function test() {
			return $this->belongsTo(TestModelEloquentBuilder::class, 'test_table_id');
		}
	}