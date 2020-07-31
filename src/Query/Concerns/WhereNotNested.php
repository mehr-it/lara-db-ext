<?php


	namespace MehrIt\LaraDbExt\Query\Concerns;


	use Closure;

	trait WhereNotNested
	{

		/**
		 * Add a nested where statement to the query prefixed with NOT
		 *
		 * @param Closure $callback
		 * @param string $boolean
		 * @return \Illuminate\Database\Query\Builder|static
		 */
		public function whereNotNested(Closure $callback, $boolean = 'and') {
			return parent::whereNested($callback, $boolean . ' not');
		}

		/**
		 * Add a nested where or-statement to the query prefixed with NOT
		 *
		 * @param Closure $callback
		 * @return \Illuminate\Database\Query\Builder|static
		 */
		public function orWhereNotNested(Closure $callback) {
			return $this->whereNotNested($callback, 'or');
		}
	}