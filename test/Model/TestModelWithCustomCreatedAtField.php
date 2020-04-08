<?php


	namespace MehrItLaraDbExtTest\Model;


	class TestModelWithCustomCreatedAtField extends TestModel
	{
		const CREATED_AT = 'cre_field';

		protected $table = 'test_table';

	}