<?php


	namespace MehrItLaraDbExtTest\Model;


	use Illuminate\Database\Eloquent\Model;
	use MehrIt\LaraDbExt\Model\DbExtensions;

	class TestModelWithoutPrimaryKey extends Model
	{
		use DbExtensions;

		protected $table = 'test_table';

		protected $primaryKey = null;

	}