<?php


	namespace MehrIt\LaraDbExt\Query\Concerns;


	use Generator;
	use RuntimeException;
	use stdClass;

	trait ChunkedGenerateById
	{
		/**
		 *  Returns a generator which queries results in chunks internally by comparing numeric IDs
		 * @param int $queryChunkSize The query chunk size
		 * @param string $column The id column name
		 * @param string|null $alias The alias which is used in the query for the id column
		 * @param callable|null $callback An optional callback to process each data chunk before yielding the items. The callback must return an iterable with the chunk items.
		 * @return Generator|stdClass[] The generator yielding all queried items
		 */
		public function generateChunkedById(int $queryChunkSize = 500, string $column = 'id', string $alias = null, callable $callback = null) {

			$alias = $alias ?: $column;

			$lastId = null;

			do {
				$clone = clone $this;

				$results = $clone->forPageAfterId($queryChunkSize, $lastId, $column)->get();

				$count = $results->count();

				if ($count == 0)
					break;

				$lastId = $results->last()->{$alias};

				// invoke callback if one exists
				if ($callback && $count) {
					$results = call_user_func($callback, $results);

					if (!is_iterable($results))
						throw new RuntimeException('Callback passed to generateChunked() must return an iterable, got ' . (is_object($results) ? get_class($results) : strtolower(gettype($results))));
				}

				// yield results (do not use yield from, to generate new unique keys)
				foreach ($results as $curr) {
					yield $curr;
				}

				unset($results);

			} while ($count == $queryChunkSize);

		}
	}