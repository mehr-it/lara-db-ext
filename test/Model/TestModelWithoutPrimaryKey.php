<?php


	namespace MehrItLaraDbExtTest\Model;


	use Illuminate\Database\Eloquent\Model;

	class TestModelWithoutPrimaryKey extends Model
	{
		protected $table = 'test_table';

		protected $primaryKey = null;

	}