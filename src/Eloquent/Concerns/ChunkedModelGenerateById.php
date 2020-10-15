<?php


	namespace MehrIt\LaraDbExt\Eloquent\Concerns;


	use Generator;
	use Illuminate\Contracts\Support\Arrayable;
	use Illuminate\Database\Eloquent\Model;

	trait ChunkedModelGenerateById
	{
		/**
		 * Returns a generator which queries results in chunks internally by comparing numeric IDs
		 * @param int $queryChunkSize The query chunk size
		 * @param string|null $column The id column name. If null, the model key will be used.
		 * @param string|null $alias The alias which is used in the query for the id column
		 * @param callable|null $callback An optional callback to process each data chunk before yielding the items. The callback must return an iterable with the chunk items.
		 * @return Generator|Model[] The generator yielding all queried items
		 */
		public function generateChunkedById(int $queryChunkSize = 500, string $column = null, string $alias = null, callable $callback = null) {

			$this->enforceOrderBy();

			$column = is_null($column) ? $this->getModel()->getKeyName() : $column;

			yield from $this->applyScopes()->query->generateChunkedById($queryChunkSize, $column, $alias, function (Arrayable $chunk) use ($callback) {

				// create models from data
				$chunk = $this->hydrate($chunk->toArray());

				if ($callback)
					$chunk = call_user_func($callback, $chunk);

				return $chunk;

			});

		}
	}