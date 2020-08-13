<?php


	namespace MehrIt\LaraDbExt\Connection\Connections;


	use MehrIt\LaraDbExt\Connection\AdaptsTimezone;
	use MehrIt\LaraDbExt\Connection\CreatesQueryBuilder;
	use MehrIt\LaraDbExt\Connection\Forkable;
	use MehrIt\LaraDbExt\Connection\ForksSelf;
	use MehrIt\LaraDbExt\Connection\SqlMode;
	use MehrIt\LaraDbExt\Query\Grammars\PostgresGrammar;

	class PostgresConnection extends \Illuminate\Database\PostgresConnection implements Forkable
	{
		use AdaptsTimezone;
		use CreatesQueryBuilder;
		use ForksSelf;
		use SqlMode;

		/**
		 * @inheritDoc
		 */
		protected function getDefaultQueryGrammar() {

			// use our custom query grammar
			return $this->withTablePrefix(new PostgresGrammar());
		}
	}