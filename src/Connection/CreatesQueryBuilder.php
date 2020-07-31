<?php


	namespace MehrIt\LaraDbExt\Connection;


	use MehrIt\LaraDbExt\Query\Builder;

	trait CreatesQueryBuilder
	{

		/**
		 * Get a new query builder instance.
		 *
		 * @return \MehrIt\LaraDbExt\Query\Builder
		 */
		public function query() {
			return app(Builder::class, [
				'connection' => $this,
				'grammar'    => $this->getQueryGrammar(),
				'processor'  => $this->getPostProcessor(),
			]);
		}

	}