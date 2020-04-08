<?php


	namespace MehrItLaraDbExtTest\Model;


	class TestModelWithCustomUpdatedAtField extends TestModel
	{
		const UPDATED_AT = 'upd_field';

		protected $table = 'test_table';

	}