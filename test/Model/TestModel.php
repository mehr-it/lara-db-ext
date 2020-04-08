<?php


	namespace MehrItLaraDbExtTest\Model;


	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Database\Eloquent\Model;

	/**
	 * Class TestModel
	 * @package MehrItLaraDbExtTest\Model
	 *
	 * @mixin Builder
	 */
	class TestModel extends Model
	{
		protected $table = 'test_table';
	}