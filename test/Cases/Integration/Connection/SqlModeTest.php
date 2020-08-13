<?php


	namespace MehrItLaraDbExtTest\Cases\Integration\Connection;


	use DB;
	use Exception;
	use Illuminate\Database\Connection;
	use MehrIt\LaraDbExt\Connection\SqlMode;
	use MehrItLaraDbExtTest\Cases\TestCase;

	class SqlModeTest extends TestCase
	{
		public function testWithSqlModeDisabled() {

			/** @var Connection|SqlMode $connection */
			$connection = DB::connection();


			$this->assertStringContainsString('ONLY_FULL_GROUP_BY', $connection->select('SELECT @@sql_mode')[0]->{'@@sql_mode'});

			$ret = $connection->withSqlModeDisabled('ONLY_FULL_GROUP_BY', function () use ($connection) {

				$this->assertStringNotContainsString('ONLY_FULL_GROUP_BY', $connection->select('SELECT @@sql_mode')[0]->{'@@sql_mode'});

				return 19;
			});

			$this->assertStringContainsString('ONLY_FULL_GROUP_BY', $connection->select('SELECT @@sql_mode')[0]->{'@@sql_mode'});
			$this->assertSame(19, $ret);


		}

		public function testWithSqlModeDisabled_throwingException() {

			/** @var Connection|SqlMode $connection */
			$connection = DB::connection();


			$this->assertStringContainsString('ONLY_FULL_GROUP_BY', $connection->select('SELECT @@sql_mode')[0]->{'@@sql_mode'});

			try {
				$connection->withSqlModeDisabled('ONLY_FULL_GROUP_BY', function () use ($connection) {

					$this->assertStringNotContainsString('ONLY_FULL_GROUP_BY', $connection->select('SELECT @@sql_mode')[0]->{'@@sql_mode'});

					throw new Exception();
				});

				$this->fail('The expected exception was not thrown');
			}
			catch (Exception $ex) {

			}
			$this->assertStringContainsString('ONLY_FULL_GROUP_BY', $connection->select('SELECT @@sql_mode')[0]->{'@@sql_mode'});



		}
	}