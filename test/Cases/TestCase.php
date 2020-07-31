<?php


	namespace MehrItLaraDbExtTest\Cases;


	use Carbon\Carbon;
	use Illuminate\Database\Connection;
	use Illuminate\Support\Arr;
	use MehrIt\LaraDbExt\Provider\LaraDbExtServiceProvider;

	class TestCase extends \Orchestra\Testbench\TestCase
	{
		use CreatesTestDatabase;

		protected $connectionsToTransact = [null, 'adapt-timezone-connection'];

		protected function cleanTables() {

		}

		protected function matchesExpectedSql($expectedSql) {
			return $this->callback(function ($value) use ($expectedSql) {

				$valueNorm = $this->normalizeSql($value);


				$expectedSql = Arr::wrap($expectedSql);

				foreach ($expectedSql as $curr) {
					if ($this->normalizeSql($curr) === $valueNorm)
						return true;
				}

				$this->assertEquals($expectedSql[0], $value);

				return false;
			});
		}

		protected function normalizeSql($sql) {
			$sql = preg_replace('/([^\w])\s+/', '$1', $sql);
			$sql = preg_replace('/\s+([^\w])/', '$1', $sql);

			$sql = preg_replace('/\s+/', ' ', $sql);


			return $sql;
		}

		protected function setUp(): void {
			parent::setUp();

			Carbon::setTestNow(null);

			$this->cleanTables();

			$this->withFactories(__DIR__ . '/../database/factories');

			// register dummy driver
			Connection::resolverFor('dummyMocked', function ($connection, $database, $prefix, $config) {
				return new Connection($connection, $database, $prefix, $config);
			});
		}


		/**
		 * @inheritDoc
		 */
		protected function setUpTraits() {
			$this->setupTestingMigrations(__DIR__ . '/../database/migrations');

			return parent::setUpTraits();
		}

		/**
		 * Load package service provider
		 * @param \Illuminate\Foundation\Application $app
		 * @return array
		 */
		protected function getPackageProviders($app) {
			return [
				LaraDbExtServiceProvider::class,
			];
		}

		/**
		 * Define environment setup.
		 *
		 * @param \Illuminate\Foundation\Application $app
		 * @return void
		 */
		protected function getEnvironmentSetUp($app) {

			$app['config']->set('database.connections.dummyMocked', [
				'driver'   => 'dummyMocked',
				'database' => 'db',
				'prefix'   => '',
			]);

			$app['config']->set('database.connections.dummyMockedPrefixed', [
				'driver'   => 'dummyMocked',
				'database' => 'db',
				'prefix'   => 'myPfx_',
			]);

			// create testing connection with adapt_timezone
			$defaultConnectionName    = config('database.default');
			$config                   = config("database.connections.$defaultConnectionName");
			$config['adapt_timezone'] = true;
			config()->set("database.connections.adapt-timezone-connection", $config);

		}
	}