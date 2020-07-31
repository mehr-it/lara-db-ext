<?php


	namespace MehrIt\LaraDbExt\Query\Concerns;


	use Generator;
	use Illuminate\Database\Eloquent\Model;
	use RuntimeException;
	use stdClass;

	trait ChunkedGenerate
	{

		/**
		 *  Returns a generator which queries results in chunks internally
		 * @param int $queryChunkSize The query chunk size
		 * @param callable|null $callback An optional callback to process each data chunk before yielding the items. The callback must return an iterable with the chunk items.
		 * @return Generator|stdClass[] The generator yielding all queried items
		 */
		public function generateChunked(int $queryChunkSize = 500, callable $callback = null) {

			$this->enforceOrderBy();

			$page = 1;

			do {
				$results = $this->forPage($page, $queryChunkSize)->get();

				$count = $results->count();

				// invoke callback if one exists
				if ($callback && $count) {
					$results = call_user_func($callback, $results, $page);

					if (!is_iterable($results))
						throw new RuntimeException('Callback passed to generateChunked() must return an iterable, got ' . (is_object($results) ? get_class($results) : strtolower(gettype($results))));
				}

				// yield results (do not use yield from, to generate new unique keys)
				foreach ($results as $curr) {
					yield $curr;
				}

				unset($results);

				++$page;

			} while ($count >= $queryChunkSize);
		}

	}