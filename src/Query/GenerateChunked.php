<?php


	namespace MehrIt\LaraDbExt\Query;


	use Generator;
	use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
	use Illuminate\Database\Query\Builder as QueryBuilder;
	use InvalidArgumentException;
	use RuntimeException;

	class GenerateChunked
	{
		/**
		 * @var QueryBuilder
		 *
		 */
		protected $builder;

		/**
		 * @var int
		 */
		protected $queryChunkSize;

		/**
		 * @var callable|null
		 */
		protected $callback;

		/**
		 * Creates a new instance
		 * @param QueryBuilder|EloquentBuilder $builder The builder instance
		 * @param int $queryChunkSize The query chunk size
		 * @param callable|null $callback An optional callback to process each data chunk before yielding the items. The callback must return an iterable with the chunk items.
		 */
		public function __construct($builder, int $queryChunkSize = 500, callable $callback = null) {

			if ($queryChunkSize < 1)
				throw new InvalidArgumentException('Query chunk size must be greater than 0');

			$this->builder        = $builder;
			$this->queryChunkSize = $queryChunkSize;
			$this->callback       = $callback;
		}


		/**
		 * Executes the query
		 * @return Generator The generator yielding all queried items
		 */
		public function execute() {

			$this->enforceOrderBy();

			$page           = 1;
			$queryChunkSize = $this->queryChunkSize;
			$callback       = $this->callback;
			$builder        = $this->builder;

			do {
				$results = $builder->forPage($page, $queryChunkSize)->get();

				$count = $results->count();

				// invoke callback if one exists
				if ($callback && $count) {
					$results = call_user_func($callback, $results, $page);

					if (!is_iterable($results))
						throw new RuntimeException('Callback must return an iterable, got ' . (is_object($results) ? get_class($results) : strtolower(gettype($results))));
				}

				// yield results (do not use yield from, to generate new unique keys)
				foreach($results as $curr) {
					yield $curr;
				}

				unset($results);

				++$page;

			} while ($count >= $queryChunkSize);

		}

		/**
		 * Enforces that the query has an order by clause
		 */
		protected function enforceOrderBy() {

			$builder = $this->builder;

			// enforce order by
			if (empty($builder->orders) && empty($builder->unionOrders))
				throw new RuntimeException('You must specify an orderBy clause when using this function');
		}

	}