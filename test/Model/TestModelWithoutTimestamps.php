<?php


	namespace MehrItLaraDbExtTest\Model;


	class TestModelWithoutTimestamps extends TestModel
	{
		protected $table = 'test_table';

		public $timestamps = false;
	}