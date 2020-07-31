<?php


	namespace MehrIt\LaraDbExt\Query\Grammars;


	use MehrIt\LaraDbExt\Query\Builder;

	class SQLiteGrammar extends \Illuminate\Database\Query\Grammars\SQLiteGrammar
	{
		use CompilesCommonTableExpressions;

		/**
		 * Creates a new instance
		 */
		public function __construct() {
			$this->initCommonTableExpressions();
		}

		/**
		 * Compile a single union statement.
		 *
		 * @param array $union
		 * @return string
		 */
		protected function compileUnion(array $union) {
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8);

			if (($backtrace[6]['class'] === Builder::class && $backtrace[6]['function'] === 'withExpression')
			    || ($backtrace[7]['class'] === Builder::class && $backtrace[7]['function'] === 'withExpression')) {
				$conjunction = $union['all'] ? ' union all ' : ' union ';

				return $conjunction . $union['query']->toSql();
			}

			return parent::compileUnion($union);
		}
	}