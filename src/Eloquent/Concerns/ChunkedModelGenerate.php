<?php


	namespace MehrIt\LaraDbExt\Eloquent\Concerns;


	use Generator;
	use Illuminate\Contracts\Support\Arrayable;
	use Illuminate\Database\Eloquent\Model;

	trait ChunkedModelGenerate
	{

		/**
		 *  Returns a generator which queries results in chunks internally
		 * @param int $queryChunkSize The query chunk size
		 * @param callable|null $callback An optional callback to process each data chunk before yielding the items. The callback must return an iterable with the chunk items.
		 * @return Generator|Model[] The generator yielding all queried items
		 */
		public function generateChunked(int $queryChunkSize = 500, callable $callback = null) {

			$this->enforceOrderBy();

			yield from $this->applyScopes()->query->generateChunked($queryChunkSize, function(Arrayable $chunk, $page) use ($callback) {

				// create models from data
				$chunk = $this->hydrate($chunk->toArray());

				if ($callback)
					$chunk = call_user_func($callback, $chunk, $page);

				return $chunk;

			});

		}

	}