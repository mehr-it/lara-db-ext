<?php


	namespace MehrIt\LaraDbExt\Eloquent\Concerns;


	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Arr;

	trait ModelData
	{
		/**
		 * Extracts the specified data fields from given models
		 * @param Model[] $models The models
		 * @param string[] $fields The fields to extract
		 * @param bool|string $withTimestamps True if to set both timestamps. 'created' or 'updated' for a single timestamp. False if not to set timestamp data.
		 * @return array
		 */
		protected function extractModelDataForQuery(array $models, array $fields, $withTimestamps = true): array {

			$model = $this->getModel();

			// get timestamp fields
			$timestampFields = [];
			if ($withTimestamps && $model->usesTimestamps()) {

				if (($withTimestamps === true || $withTimestamps === 'created') && ($createdAtField = $model->getCreatedAtColumn()) !== null)
					$timestampFields[] = $createdAtField;

				if (($withTimestamps === true || $withTimestamps === 'updated') && ($updateAtField = $model->getUpdatedAtColumn()) !== null)
					$timestampFields[] = $updateAtField;
			}

			$now = $model->freshTimestampString();

			// build default values for all fields to be inserted (we sort them, so later functions already get all fields in same order, which is faster)
			$fieldDefaults = array_fill_keys(array_merge($fields, $timestampFields), null);
			ksort($fieldDefaults);

			$data = [];
			foreach ($models as $currModel) {
				$currRow = $currModel->getAttributes();

				// set timestamp fields if not already modified in model
				foreach ($timestampFields as $currTimestampField) {
					if (($currRow[$currTimestampField] ?? null) === null || $currModel->isClean($currTimestampField))
						$currRow[$currTimestampField] = $now;
				}

				// Add data. Only the fields to update. Missing fields default to null.
				$data[] = array_merge($fieldDefaults, array_intersect_key($currRow, $fieldDefaults));
			}

			return $data;
		}

		/**
		 * Gets a column list using all set attributes of the first model
		 * @param Model[] $models The models
		 * @return string[] The column names
		 */
		protected function columnListFromModels(array $models): array {
			/** @var Model $firstRow */
			$firstRow = Arr::first($models);

			return array_keys($firstRow->getAttributes());
		}
	}