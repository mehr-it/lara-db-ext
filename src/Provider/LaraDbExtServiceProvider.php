<?php


	namespace MehrIt\LaraDbExt\Provider;


	use Illuminate\Support\ServiceProvider;
	use MehrIt\LaraDbExt\Connection\Connections\MySqlConnection;
	use MehrIt\LaraDbExt\Connection\Connections\PostgresConnection;
	use MehrIt\LaraDbExt\Connection\Connections\SQLiteConnection;
	use MehrIt\LaraDbExt\Connection\Connections\SqlServerConnection;
	use MehrIt\LaraDbExt\Eloquent\Builder as EloquentBuilder;
	use MehrIt\LaraDbExt\Query\Builder as QueryBuilder;
	use MehrIt\LaraDbExt\Query\QueryManager;

	class LaraDbExtServiceProvider extends ServiceProvider
	{
		use RegistersConnection;

		public $singletons = [
			QueryManager::class => QueryManager::class,
		];

		public function boot() {

			// register connections
			$this->registerConnection('mysql', MySqlConnection::class);
			$this->registerConnection('pgsql', PostgresConnection::class);
			$this->registerConnection('sqlite', SQLiteConnection::class);
			$this->registerConnection('sqlsrv', SqlServerConnection::class);


			// register builders
			app()->bind(QueryBuilder::class, function ($a, $params) {
				return new QueryBuilder(
					$params['connection'],
					$params['grammar'] ?? null,
					$params['processor'] ?? null
				);
			});
			app()->bind(EloquentBuilder::class, function($app, $params) {
				return new EloquentBuilder(
					$params['query'] ?? $app(QueryBuilder::class)
				);
			});


		}

	}