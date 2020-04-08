<?php


	namespace MehrItLaraDbExtTest\Model;


	class TestModelWithoutUpdatedAtField extends TestModel
	{
		const UPDATED_AT = null;

		protected $table = 'test_table';

	}