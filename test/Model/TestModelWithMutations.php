<?php


	namespace MehrItLaraDbExtTest\Model;


	use Illuminate\Database\Eloquent\Model;
	use MehrIt\LaraDbExt\Model\DbExtensions;

	class TestModelWithMutations extends Model
	{
		use DbExtensions;

		protected $table = 'test_table';

		protected $dates = [
			'dt',
		];

		public function setNameAttribute($value) {
			$this->attributes['name'] = strtolower($value);
		}

	}