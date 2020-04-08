<?php


	namespace MehrItLaraDbExtTest\Model;


	class TestModelWithoutCreatedAtField extends TestModel
	{
		const CREATED_AT = null;

		protected $table = 'test_table';

	}