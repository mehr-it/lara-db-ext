<?php


	namespace MehrIt\LaraDbExt\Query;


	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use MehrIt\LaraDbExt\Query\Concerns\AutoDetectWhereIn;
	use MehrIt\LaraDbExt\Query\Concerns\ChunkedGenerate;
	use MehrIt\LaraDbExt\Query\Concerns\ChunkedGenerateById;
	use MehrIt\LaraDbExt\Query\Concerns\CommonTableExpressions;
	use MehrIt\LaraDbExt\Query\Concerns\InsertOnDuplicateKey;
	use MehrIt\LaraDbExt\Query\Concerns\SelectPrefixed;
	use MehrIt\LaraDbExt\Query\Concerns\UpdateWithJoinedData;
	use MehrIt\LaraDbExt\Query\Concerns\WhereMultipleColumns;
	use MehrIt\LaraDbExt\Query\Concerns\WhereMultipleIn;
	use MehrIt\LaraDbExt\Query\Concerns\WhereNotNested;

	class Builder extends \Illuminate\Database\Query\Builder
	{
		use AutoDetectWhereIn;
		use ChunkedGenerate;
		use ChunkedGenerateById;
		use CommonTableExpressions;
		use InsertOnDuplicateKey;
		use SelectPrefixed;
		use UpdateWithJoinedData;
		use WhereMultipleColumns;
		use WhereMultipleIn;
		use WhereNotNested;

		public function __construct(ConnectionInterface $connection, Grammar $grammar = null, Processor $processor = null) {
			parent::__construct($connection, $grammar, $processor);

			$this->initCommonTableExpressions();
		}


		/**
		 * @inheritDoc
		 */
		public function where($column, $operator = null, $value = null, $boolean = 'and') {
			return $this->detectWhereIn(function () {
				return parent::where(...func_get_args());
			}, ...func_get_args());
		}

		/**
		 * @inheritDoc
		 */
		public function whereIn($column, $values, $boolean = 'and', $not = false) {

			return $this->handleWhereMultipleIn(function () {
				return parent::whereIn(...func_get_args());
			}, ...func_get_args());
		}

		/**
		 * @inheritDoc
		 */
		public function whereColumn($first, $operator = null, $second = null, $boolean = 'and') {
			return $this->handleWhereMultipleColumns(function () {
				return parent::whereColumn(...func_get_args());
			}, ...func_get_args());
		}




	}