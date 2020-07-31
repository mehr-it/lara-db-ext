<?php


	namespace MehrIt\LaraDbExt\Eloquent\Concerns;


	use Illuminate\Contracts\Support\Arrayable;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Arr;
	use Traversable;

	trait InsertModels
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
		 * Inserts multiple models
		 * @param Traversable|Arrayable|Model[] $models The models to insert
		 * @param string[] $fields Specifies the fields to insert. If omitted, the first given model's attributes are used as field list.
		 * @param bool $withTimestamps True if to add timestamps. Else false.
		 * @return bool
		 */
		public function insertModels($models, array $fields = [], bool $withTimestamps = true) {

			if ($models instanceof Arrayable)
				$models = $models->toArray();
			if ($models instanceof Traversable)
				$models = iterator_to_array($models);

			if (empty($models))
				return true;

			// get columns from first model if not passed
			if (!$fields)
				$fields = $this->columnListFromModels($models);

			$data = $this->extractModelDataForQuery($models, $fields, $withTimestamps);

			return $this->insert($data);
		}

	}