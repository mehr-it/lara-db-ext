<?php


	namespace MehrIt\LaraDbExt\Model;


	use Illuminate\Database\Query\Builder;

	trait CreatesBuilders
	{
		/**
		 * Get a new query builder instance for the connection.
		 *
		 * @return Builder
		 */
		protected function newBaseQueryBuilder() {


			$connection = $this->getConnection();


			// we resolve instance here using service container
			return app(\MehrIt\LaraDbExt\Query\Builder::class, [
				'connection' => $connection,
			]);
		}

		/**
		 * Create a new Eloquent query builder for the model.
		 *
		 * @param \Illuminate\Database\Query\Builder $query
		 * @return \Illuminate\Database\Eloquent\Builder|static
		 */
		public function newEloquentBuilder($query) {

			// we resolve instance here using service container
			$ret = app(\MehrIt\LaraDbExt\Eloquent\Builder::class, [
				'query' => $query,
			]);


			return $ret;
		}
	}