<?php


	namespace MehrIt\LaraDbExt\Query\Concerns;



	use MehrIt\LaraDbExt\Query\Builder;

	trait WhereMultipleColumns
	{
		/**
		 * Handles an array of columns passed instead of a single column
		 * @param callable $callback The callback to invoke
		 * @param string|string[] $first The left column(s)
		 * @param string|null|string[] $operator The operator
		 * @param string|string[] null $second The right column(s)
		 * @param string $boolean The boolean operator
		 * @return mixed The callback return
		 */
		protected function handleWhereMultipleColumns($callback, $first, $operator = null, $second = null, $boolean = 'and') {

			$args = func_get_args();

			// If the given operator is not found in the list of valid operators we will
			// assume that the developer is just short-cutting the '=' operators and
			// we will set the operators to '=' and set the values appropriately.
			if (is_array($operator) || $this->invalidOperator($operator))
				[$second, $operator] = [$operator, '='];


			if (is_array($first) && is_array($second) && in_array($operator, ['=', '<>', '!='])) {
				$wheres = [];

				foreach (array_map(null, $first, $second) as $curr) {
					$wheres[] = [$curr[0], '=', $curr[1]];
				}

				return call_user_func($callback, $wheres, null, null, $boolean . ($operator != '=' ? ' not' : ''));

			}

			// call the parent function
			array_shift($args);

			return call_user_func_array($callback, $args);
		}

		/**
		 * Add a "where" clause comparing multiple columns to the query.
		 *
		 * @param array $first
		 * @param string|null $operator
		 * @param array|null $second
		 * @param string|null $boolean
		 * @return Builder
		 */
		public function whereMultipleColumns($first, $operator = null, $second = null, $boolean = 'and') {
			return $this->handleWhereMultipleColumns(function () {
				return parent::whereColumn(...func_get_args());
			}, ...func_get_args());
		}

		/**
		 * Add a "where" clause comparing multiple columns to the query.
		 *
		 * @param array $first
		 * @param string|null $operator
		 * @param array|null $second
		 * @return Builder
		 */
		public function orWhereMultipleColumns($first, $operator = null, $second = null) {
			return $this->whereMultipleColumns($first, $operator, $second, 'or');
		}
	}