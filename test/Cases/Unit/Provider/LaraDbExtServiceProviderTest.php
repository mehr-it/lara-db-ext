<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Provider;


	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\Connectors\ConnectionFactory;
	use DB;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use MehrIt\LaraDbExt\Connection\Connections\MySqlConnection;
	use MehrIt\LaraDbExt\Connection\Connections\PostgresConnection;
	use MehrIt\LaraDbExt\Connection\Connections\SQLiteConnection;
	use MehrIt\LaraDbExt\Connection\Connections\SqlServerConnection;
	use MehrIt\LaraDbExt\Query\Builder;
	use MehrIt\LaraDbExt\Query\QueryManager;
	use MehrItLaraDbExtTest\Cases\TestCase;

	class LaraDbExtServiceProviderTest extends TestCase
	{

		public function testConnectionsResolveQueryBuilder() {

			$queryBuilderMock = $this->getMockBuilder(Builder::class)->disableOriginalConstructor()->getMock();

			app()->bind(Builder::class, function($a, $params) use ($queryBuilderMock) {

				$this->assertSame($params['connection'], DB::connection());
				$this->assertSame($params['grammar'], DB::connection()->getQueryGrammar());
				$this->assertSame($params['processor'], DB::connection()->getPostProcessor());

				return $queryBuilderMock;
			});

			$this->assertSame($queryBuilderMock, DB::connection()->query());

		}

		public function testQueryManagerRegistered() {

			$instance = app(QueryManager::class);

			$this->assertInstanceOf(QueryManager::class, $instance);
			$this->assertSame($instance, app(QueryManager::class));

		}

		public function testQueryBuilderRegistered() {

			$conn      = $this->getMockBuilder(ConnectionInterface::class)->getMock();
			$grammar   = $this->getMockBuilder(Grammar::class)->getMock();
			$processor = $this->getMockBuilder(Processor::class)->getMock();

			/** @var Builder $builder */
			$builder = app(Builder::class, [
				'connection' => $conn,
				'grammar'    => $grammar,
				'processor'  => $processor,
			]);

			$this->assertInstanceOf(Builder::class, $builder);

			$this->assertSame($conn, $builder->getConnection());
			$this->assertSame($grammar, $builder->getGrammar());
			$this->assertSame($processor, $builder->getProcessor());

		}

		public function testEloquentBuilderRegistered() {

			$baseMock      = $this->getMockBuilder(Builder::class)->disableOriginalConstructor()->getMock();

			/** @var \MehrIt\LaraDbExt\Eloquent\Builder $builder */
			$builder = app(\MehrIt\LaraDbExt\Eloquent\Builder::class, [
				'query' => $baseMock,
			]);

			$this->assertInstanceOf(\MehrIt\LaraDbExt\Eloquent\Builder::class, $builder);

			$this->assertSame($baseMock, $builder->getQuery());
		}



		public function testConnectionsRegistered() {
			/** @var ConnectionFactory $factory */
			$factory = app('db.factory');

			$this->assertInstanceOf(MySqlConnection::class, $factory->make([
				'driver'   => 'mysql',
				'database' => 'test',
			]));
			$this->assertInstanceOf(PostgresConnection::class, $factory->make([
				'driver'   => 'pgsql',
				'database' => 'test',
			]));
			$this->assertInstanceOf(SQLiteConnection::class, $factory->make([
				'driver'   => 'sqlite',
				'database' => 'test',
			]));
			$this->assertInstanceOf(SqlServerConnection::class, $factory->make([
				'driver'   => 'sqlsrv',
				'database' => 'test',
			]));
		}

	}