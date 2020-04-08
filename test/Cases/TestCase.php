<?php


	namespace MehrItLaraDbExtTest\Cases;


	use Carbon\Carbon;
	use Illuminate\Support\Arr;
	use Illuminate\Support\Str;
	use MehrIt\LaraDbExt\Provider\LaraDbExtServiceProvider;

	class TestCase extends \Orchestra\Testbench\TestCase
	{
		use CreatesTestDatabase;

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

				$this->assertEquals($value, $expectedSql[0]);

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
	}