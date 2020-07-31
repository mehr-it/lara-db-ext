<?php


	namespace MehrIt\LaraDbExt\Query\Grammars;


	class PostgresGrammar extends \Illuminate\Database\Query\Grammars\PostgresGrammar
	{
		use CompilesCommonTableExpressions;

		/**
		 * Creates a new instance
		 */
		public function __construct() {
			$this->initCommonTableExpressions();
		}
	}