<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Eloquent;

	use Illuminate\Database\Events\StatementPrepared;
	use MehrIt\LaraDbExt\Query\Builder as ExtBuilder;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Mock\ConnectionMockBase;
	use MehrItLaraDbExtTest\Mock\PdoStatementMock;

	if (version_compare(PHP_VERSION, '8.0.0', '>='))
		require_once __DIR__ . '/../../../Mock/PdoStatementMock_8.php';
	elseif (version_compare(PHP_VERSION, '7.4.0', '>='))
		require_once __DIR__ . '/../../../Mock/PdoStatementMock_7.4.php';
	else
		require_once __DIR__ . '/../../../Mock/PdoStatementMock_7.3.php';

	class ConnectionMock extends ConnectionMockBase
	{
		/**
		 * Run a select statement against the database.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @param bool $useReadPdo
		 * @return array
		 */
		public function select($query, $bindings = [], $useReadPdo = true) {

			$result = parent::select($query, $bindings, $useReadPdo);

			$stmt = new PdoStatementMock($result ? array_keys($result[0]) : []);

			event(new StatementPrepared(
				$this, $stmt
			));

			return $result ? array_values($result) : null;
		}


		/**
		 * Run a select statement against the database and returns a generator.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @param bool $useReadPdo
		 * @return \Generator
		 */
		public function cursor($query, $bindings = [], $useReadPdo = true) {

			$result = $this->select($query, $bindings, $useReadPdo);

			yield from ($result ?: new \EmptyIterator());
		}

	}

	class Builder extends ExtBuilder
	{
		/**
		 * Run the query as a "select" statement against the connection.
		 *
		 * @return array
		 */
		protected function runSelect() {

			return array_map(function ($item) {

				// The select prefix trait converts all items to stdClass, as the database would return it
				// in "real life".
				//
				// However laravel's tests use arrays to simulate results. So we have to convert everything back
				// to arrays here, so that test assertions do not fail
				return (array)$item;

			}, parent::runSelect());

		}


	}

	$origFiles = [
		__DIR__ . '/../../../../vendor/laravel/framework/tests/Database/DatabaseEloquentBuilderTest.php' => function ($content) {

			$content = str_replace('PHPUnit\Framework\TestCase', TestCase::class, $content);
			//$content = str_replace('Illuminate\Database\Query\Builder',  Builder::class , $content);
			$content = str_replace('Illuminate\Database\Query\Builder', Builder::class, $content);
			$content = str_replace('Illuminate\Database\Eloquent\Builder', \MehrIt\LaraDbExt\Eloquent\Builder::class, $content);


			// select prefixed always uses 'cursor' instead of 'select'
			$content = preg_replace('/m::mock\\(ConnectionInterface::class([.*?])\\)/', '(new \\' . \MehrItLaraDbExtTest\Cases\Unit\Query\ConnectionMock::class . '(m::mock(ConnectionInterface::class$1)))', $content);

			// ignore some tests
			//$content = preg_replace('/(public[\\s]+function[\\s]+testWheresWithArrayValue[\\s]*\\([\\s]*\\)[\\s]*\\{[\\s])/m', '$1 \\$this->markTestSkipped("Laravel developers do not want automatic whereIn detection and added a test to avoid pull requests. But we want it, so we ignore this test...");', $content);

			return $content;
		},
		__DIR__ . '/../../../../vendor/laravel/framework/tests/Database/DatabaseEloquentModelTest.php' => function ($content) {

			$content = str_replace('PHPUnit\Framework\TestCase', TestCase::class, $content);
			//$content = str_replace('Illuminate\Database\Query\Builder',  Builder::class , $content);
			$content = str_replace('Illuminate\Database\Query\Builder', Builder::class, $content);
			$content = str_replace('Illuminate\Database\Eloquent\Builder', \MehrIt\LaraDbExt\Eloquent\Builder::class, $content);


			// select prefixed always uses 'cursor' instead of 'select'
			//$content = preg_replace('/m::mock\\(ConnectionInterface::class([.*?])\\)/', '(new \\' . \MehrItLaraDbExtTest\Cases\Unit\Query\ConnectionMock::class . '(m::mock(ConnectionInterface::class$1)))', $content);

			// ignore some tests
			//$content = preg_replace('/(public[\\s]+function[\\s]+testWheresWithArrayValue[\\s]*\\([\\s]*\\)[\\s]*\\{[\\s])/m', '$1 \\$this->markTestSkipped("Laravel developers do not want automatic whereIn detection and added a test to avoid pull requests. But we want it, so we ignore this test...");', $content);

			return $content;
		}
	];

	foreach ($origFiles as $file => $processor) {
		$currTempFile = sys_get_temp_dir() . '/laraDbExtTest' . basename($file);

		file_put_contents($currTempFile, $processor(file_get_contents($file)));

		require $currTempFile;
	}

	class OriginalBuilderTest extends \Illuminate\Tests\Database\DatabaseEloquentBuilderTest
	{
		/**
		 * Asserts that two variables have the same type and value.
		 * Used on objects, it asserts that two variables reference
		 * the same object.
		 *
		 * @throws ExpectationFailedException
		 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
		 *
		 * @psalm-template ExpectedType
		 * @psalm-param ExpectedType $expected
		 * @psalm-assert =ExpectedType $actual
		 */
		public static function assertSame($expected, $actual, string $message = ''): void {

			// avoid problems with different quote style
			if (is_string($actual))
				$actual = str_replace('`', '"', $actual);

			parent::assertSame($expected, $actual, $message);
		}

		/**
		 * @throws ExpectationFailedException
		 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
		 */
		public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void {

			// avoid problems with different quote style
			if (is_string($haystack))
				$haystack = str_replace('`', '"', $haystack);

			parent::assertStringContainsString($needle, $haystack, $message);
		}


	}