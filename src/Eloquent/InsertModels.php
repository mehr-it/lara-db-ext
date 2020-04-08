<?php


	namespace MehrIt\LaraDbExt\Eloquent;


	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Arr;

	class InsertModels
	{

		/**
		 * @var Builder
		 */
		protected $builder;

		/**
		 * @var Model[]|iterable The models
		 */
		protected $models;

		/**
		 * @var bool True if to add timestamps
		 */
		protected $withTimestamps = true;

		/**
		 * @var string[] Insert only given fields
		 */
		protected $fields = [];

		/**
		 * Creates a new instance
		 * @param Builder $builder The builder
		 * @param iterable|Model[] $models The models
		 * @param string[] $fields Specifies the fields to insert. If omitted, the first given model's attributes are used as field list.
		 * @param bool $withTimestamps True if to add timestamps. Else false.
		 */
		public function __construct(Builder $builder, iterable $models, array $fields = [], bool $withTimestamps = true) {
			$this->builder        = $builder;
			$this->models         = $models;
			$this->withTimestamps = $withTimestamps;
			$this->fields         = $fields;
		}

		/**
		 * Executes the query
		 * @return bool
		 */
		public function execute() {

			$model = $this->builder->getModel();

			// build field list
			$fields = $this->fields ?: $this->fieldNamesFromModels();

			// get timestamp fields
			$timestampFields = [];
			if ($this->withTimestamps && $model->usesTimestamps()) {

				if (($createdAtField = $model->getCreatedAtColumn()) !== null)
					$timestampFields[] = $createdAtField;

				if (($updateAtField = $model->getUpdatedAtColumn()) !== null)
					$timestampFields[] = $updateAtField;
			}

			$now = $model->freshTimestampString();

			// build default values for all fields to be inserted (we sort them, so later functions already get all fields already in same order, which is faster)
			$fieldDefaults = array_fill_keys(array_merge($fields, $timestampFields), null);
			ksort($fieldDefaults);

			$data = [];
			foreach($this->models as $currModel) {
				$currRow = $currModel->getAttributes();

				// set timestamp fields if not already modified in model
				foreach($timestampFields as $currTimestampField) {
					if (($currRow[$currTimestampField] ?? null) === null || $currModel->isClean($currTimestampField))
						$currRow[$currTimestampField] = $now;
				}

				// Add data. Only the fields to update. Missing fields default to null.
				$data[] = array_merge($fieldDefaults, array_intersect_key($currRow, $fieldDefaults));
			}

			return $this->builder->insert($data);
		}

		/**
		 * Returns the field names examining the fields of the first model
		 * @return string[] The field names
		 */
		protected function fieldNamesFromModels(): array {

			/** @var Model $model */
			$model = Arr::first($this->models);

			return array_keys($model->getAttributes());
		}


	}