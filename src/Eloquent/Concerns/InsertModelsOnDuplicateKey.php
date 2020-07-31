<?php


	namespace MehrIt\LaraDbExt\Eloquent\Concerns;


	use Illuminate\Contracts\Support\Arrayable;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Arr;
	use Traversable;

	trait InsertModelsOnDuplicateKey
	{
		/**
		 * Extracts the specified data fields from given models
		 * @param Model[] $models The models
		 * @param string[] $fields The fields to extract
		 * @param bool|string $withTimestamps True if to set both timestamps. 'created' or 'updated' for a single timestamp. False if not to set timestamp data.
		 * @return array
		 */
		protected abstract function extractModelDataForQuery(array $models, array $fields, $withTimestamps = true): array;

		/**
		 * Gets a column list using all set attributes of the first model
		 * @param Model[] $models The models
		 * @return string[] The column names
		 */
		protected abstract function columnListFromModels(array $models): array;

		/**
		 * Insert models using ON DUPLICATE KEY UPDATE
		 * @param Traversable|Arrayable|array|Model[] $models The data to insert
		 * @param array $updateColumns The columns to update. For numeric keys the value is interpreted as column name and the column is updated with the corresponding field from the dat array. For associative keys, the key is used as column name and the value can be a constant value or an expression which is used to update the column.
		 * @param bool $withTimestamps True if to manage the timestamps
		 * @return bool
		 */
		public function insertModelsOnDuplicateKey($models, array $updateColumns = [], bool $withTimestamps = true) {

			if ($models instanceof Arrayable)
				$models = $models->toArray();
			if ($models instanceof Traversable)
				$models = iterator_to_array($models);

			if (empty($models))
				return true;

			// get columns from first model if not passed
			$fields = $this->columnListFromModels($models);

			$data = $this->extractModelDataForQuery($models, $fields, $withTimestamps);

			if (!$updateColumns) {

				$updateColumns = array_keys(Arr::first($data));

				/** @var Model $model */
				$model = $this->getModel();

				// remove created timestamp from update columns
				$createdAtColumn = $model->usesTimestamps() ? $model->getCreatedAtColumn() : null;
				if ($createdAtColumn)
					$updateColumns = array_diff($updateColumns, [$createdAtColumn]);
			}

			return $this->toBase()
				->insertOnDuplicateKey(
					$data,
					$updateColumns
				);
		}
	}