<?php


	namespace MehrIt\LaraDbExt\Query\Grammars;


	use Illuminate\Database\Query\Builder;

	class MySqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
	{
		use CompilesCommonTableExpressions;

		/**
		 * Creates a new instance
		 */
		public function __construct() {
			$this->initCommonTableExpressions();
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
			$insert = "insert into {$this->wrapTable($query->from)} ({$this->columnize($columns)}) ";

			return $insert . $this->compileExpressions($query) . ' ' . $sql;
		}
	}