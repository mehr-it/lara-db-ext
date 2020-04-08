<?php


	namespace MehrIt\LaraDbExt\Eloquent;


	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Query\Expression;
	use InvalidArgumentException;
	use MehrIt\LaraDbExt\Util\ChecksEmptyIterators;
	use MehrIt\LaraDbExt\Query\UpdateWithJoinedData;

	class UpdateWithJoinedModels
	{
		use ChecksEmptyIterators;

		protected $update;

		/**
		 * Creates a new instance
		 * @param Builder $builder The Builder instance
		 * @param iterable|Model[] $models The data to update
		 * @param array|array[] $joinOn Specifies the join conditions between the data table and the target table. Arrays are interpreted as multiple join conditions. Simple string item will join data based on the given field in both tables.
		 * @param Expression[]|string[]|mixed $updateFields The fields to update. Items with numeric indices are interpreted as field names. For other items the item value (or expression) is assigned to the field specified by the item key.
		 * @param bool $withTimestamps True if to update timestamps. Else false.
		 * @param string $dataTableName The data table alias
		 */
		public function __construct(Builder $builder, $models, array $joinOn = [], $updateFields = [], bool $withTimestamps = true, string $dataTableName = 'data') {

			$model = $builder->getModel();

			// default join to primary key field
			if (!$joinOn) {

				if (!($keyField = $model->getKeyName()))
					throw new InvalidArgumentException('Join conditions are required, because model ' . get_class($model) . ' does not have a primary key');

				$joinOn = [$keyField];
			}


			// add "updated_at" column
			$updateAtField = null;
			$defaultUpdates = [];
			if ($withTimestamps && $model->usesTimestamps() && ($updateAtField = $model->getUpdatedAtColumn()) !== null)
				$defaultUpdates[] = $updateAtField;


			// build data array from model attributes
			$data = [];
			$now  = $model->freshTimestampString();
			foreach($models as $curr) {

				if (!($curr instanceof Model))
					throw new InvalidArgumentException('Data must only contain model instances.');

				$currRow = $curr->getAttributes();

				if (!$currRow)
					throw new InvalidArgumentException('Model doest not contain any data.');

				// set updated at if not already set by model
				if ($updateAtField && (($currRow[$updateAtField] ?? null) === null || $curr->isClean($updateAtField)))
					$currRow[$updateAtField] = $now;

				$data[] = $currRow;
			}


			$this->update = (new UpdateWithJoinedData(
				$builder->toBase(),
				$data,
				$joinOn,
				$updateFields,
				$dataTableName
			))
				->setUpdateDefaultFields($defaultUpdates);

		}

		/**
		 * Executes the query
		 * @return int The number of affected rows
		 */
		public function execute() {
			return $this->update->execute();
		}
	}