<?php


	namespace MehrIt\LaraDbExt\Query\Grammars;


	use Illuminate\Database\Query\Builder;

	trait CompilesCommonTableExpressions
	{
		/**
		 * Initialize common table expressions
		 */
		public function initCommonTableExpressions() {
			array_unshift($this->selectComponents, 'expressions');

			$this->selectComponents[] = 'recursionLimit';
		}

		/**
		 * Compile the common table expressions.
		 *
		 * @param \Illuminate\Database\Query\Builder|\MehrIt\LaraDbExt\Query\Builder $query
		 * @return string
		 */
		public function compileExpressions(Builder $query) {
			if (!$query->expressions) {
				return '';
			}

			$recursive = $this->recursiveKeyword($query->expressions);

			$statements = [];

			foreach ($query->expressions as $expression) {
				$columns = $expression['columns'] ? "({$this->columnize($expression['columns'])}) " : '';

				$statements[] = "{$this->wrapTable($expression['name'])} {$columns}as ({$expression['query']})";
			}

			return 'with ' . $recursive . implode(', ', $statements);
		}

		/**
		 * Get the "recursive" keyword.
		 *
		 * @param array $expressions
		 * @return string
		 */
		protected function recursiveKeyword(array $expressions) {
			return collect($expressions)->where('recursive', true)->isNotEmpty() ? 'recursive ' : '';
		}

		/**
		 * Compile the recursion limit.
		 *
		 * @param \Illuminate\Database\Query\Builder|\MehrIt\LaraDbExt\Query\Builder $query
		 * @return string
		 */
		public function compileRecursionLimit(Builder $query) {
			if (is_null($query->recursionLimit)) {
				return '';
			}

			return 'option (maxrecursion ' . (int)$query->recursionLimit . ')';
		}

		/**
		 * Compile an insert statement using a subquery into SQL.
		 *
		 * @param \Illuminate\Database\Query\Builder $query
		 * @param array $columns
		 * @param string $sql
		 * @return string
		 */
		public function compileInsertUsing(Builder $query, array $columns, string $sql) {
			$expressions = $this->compileExpressions($query);

			$recursionLimit = $this->compileRecursionLimit($query);

			$compiled = parent::compileInsertUsing($query, $columns, $sql);

			return trim("{$expressions} {$compiled} {$recursionLimit}");
		}

		/**
		 * Compile an update statement into SQL.
		 *
		 * @param \Illuminate\Database\Query\Builder $query
		 * @param array $values
		 * @return string
		 */
		public function compileUpdate(Builder $query, $values) {
			$compiled = parent::compileUpdate($query, $values);

			return trim("{$this->compileExpressions($query)} {$compiled}");

		}

		/**
		 * Prepare the bindings for an update statement.
		 *
		 * @param array $bindings
		 * @param array $values
		 * @return array
		 */
		public function prepareBindingsForUpdate(array $bindings, array $values) {
			$values = array_merge($bindings['expressions'] ?? [], $values);

			unset($bindings['expressions']);

			return parent::prepareBindingsForUpdate($bindings, $values);
		}

		/**
		 * Compile a delete statement into SQL.
		 *
		 * @param \Illuminate\Database\Query\Builder $query
		 * @return string
		 */
		public function compileDelete(Builder $query) {
			$compiled = parent::compileDelete($query);

			return trim("{$this->compileExpressions($query)} {$compiled}");
		}
	}