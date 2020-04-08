<?php


	namespace MehrItLaraDbExtTest\Model;


	use Illuminate\Database\Eloquent\Model;

	class TestModelWithMutations extends Model
	{
		protected $table = 'test_table';

		protected $dates = [
			'dt',
		];

		public function setNameAttribute($value) {
			$this->attributes['name'] = strtolower($value);
		}

	}