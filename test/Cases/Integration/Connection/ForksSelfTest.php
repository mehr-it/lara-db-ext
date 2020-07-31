<?php


	namespace MehrItLaraDbExtTest\Cases\Integration\Connection;


	use DB;
	use Illuminate\Database\Connection;
	use MehrIt\LaraDbExt\Connection\Connections\MySqlConnection;
	use MehrIt\LaraDbExt\Connection\Forkable;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use PHPUnit\Framework\SkippedTestError;
	use RuntimeException;

	class ForksSelfTest extends TestCase
	{
		public function testFork() {

			/** @var Connection|Forkable $connection */
			$connection = DB::connection();

			$this->assertInstanceOf(Forkable::class, $connection);

			/** @var Connection $fork */
			$fork = $connection->fork();

			$this->assertNotSame($connection->getPdo(), $fork->getPdo());
			$this->assertEquals($connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME), $fork->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME));

			// test select
			$fork->select('SELECT 1');

			// check that forked connections are not kept by database manager
			$this->expectException(\InvalidArgumentException::class);
			DB::connection($fork->getName());
		}

		public function testReconnect() {
			/** @var Connection|Forkable $connection */
			$connection = DB::connection();

			if (!($connection instanceof MySqlConnection))
				throw new SkippedTestError('This test requires a MySQL connection');

			$fork = $connection->fork([], [\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false]);

			$pdoBefore = $fork->getPdo();

			// do reconnect and check again
			$fork->reconnect();

			// test select
			$fork->select('SELECT 1');

			$pdoAfter = $fork->getPdo();

			$this->assertNotSame($pdoBefore, $pdoAfter);

			// check that forked connections are not kept by database manager
			$this->expectException(\InvalidArgumentException::class);
			DB::connection($fork->getName());

		}

		public function testOverrideAttribute() {
			/** @var Connection|Forkable $connection */
			$connection = DB::connection();

			if (!($connection instanceof MySqlConnection))
				throw new SkippedTestError('This test requires a MySQL connection');

			$fork = $connection->fork([], [\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false]);

			$this->assertEquals(true, $connection->getPdo()->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));
			$this->assertEquals(false, $fork->getPdo()->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));

			// do reconnect and check again
			$connection->reconnect();
			$fork->reconnect();
			$this->assertEquals(true, $connection->getPdo()->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));
			$this->assertEquals(false, $fork->getPdo()->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY));
		}

		public function testOverrideOption() {
			/** @var Connection|Forkable $connection */
			$connection = DB::connection();

			if (!($connection instanceof MySqlConnection))
				throw new SkippedTestError('This test requires a MySQL connection');

			$fork = $connection->fork(['prefix' => 'pfx_']);

			$this->assertEquals(null, $connection->getTablePrefix());
			$this->assertEquals('pfx_', $fork->getTablePrefix());

			// do reconnect and check again
			$connection->reconnect();
			$fork->reconnect();
			$this->assertEquals(null, $connection->getTablePrefix());
			$this->assertEquals('pfx_', $fork->getTablePrefix());

		}


		public function testDestroyForked() {

			/** @var Connection|Forkable $connection */
			$connection = DB::connection();

			/** @var Connection|Forkable $fork */
			$fork = $connection->fork();

			$fork->destroyFork();

			$this->assertNull($fork->getPdo());

			// reconnect should not be possible
			$this->expectException(\InvalidArgumentException::class);
			$fork->reconnect();
		}

		public function testDestroyForked_notForkedConnection() {

			/** @var Connection|Forkable $connection */
			$connection = DB::connection();

			try {
				// this should not work but throw an exception
				$connection->destroyFork();

				$this->assertFalse(true);

			}
			catch (RuntimeException $ex) {

				// check that the connection is still intact
				$this->assertNotNull($connection->getPdo());
				$connection->reconnect();
			}


		}
	}