<?php


	namespace MehrIt\LaraDbExt\Provider;


	use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Query\Builder as QueryBuilder;
	use Illuminate\Database\Query\Expression;
	use MehrIt\LaraDbExt\Eloquent\InsertModels;
	use MehrIt\LaraDbExt\Eloquent\UpdateWithJoinedModels;
	use MehrIt\LaraDbExt\Query\UpdateWithJoinedData;

	trait RegistersBuilderMacros
	{
		/**
		 * Gets the query builder class
		 * @return string The query builder class
		 */
		protected function getQueryBuilderClass(): string {
			return QueryBuilder::class;
		}

		/**
		 * Gets the eloquent builder class
		 * @return string The eloquent builder class
		 */
		protected function getEloquentBuilderClass(): string {
			return EloquentBuilder::class;
		}

		/**
		 * Registers the macros for the query builder
		 */
		protected function registerQueryBuilderMacros() {

			$cls = $this->getQueryBuilderClass();

			$this->registerMacro(
				$cls,
				'updateWithJoinedData',
				/**
				 * Executes an update with given data joined as virtual table
				 * @param iterable[] $data The data to update
				 * @param array|array[] $joinOn Specifies the join conditions between the data table and the target table. Arrays are interpreted as multiple join conditions. Simple string item will join data based on the given field in both tables.
				 * @param string $dataTableName The data table alias
				 * @param Expression[]|string[]|mixed $updateFields The fields to update. Items with numeric indices are interpreted as field names. For other items the item value (or expression) is assigned to the field specified by the item key.
				 * @return int The number of affected rows
				 */
				function ($data, array $joinOn = ['id'], $updateFields = [], string $dataTableName = 'data') {

					/** @noinspection PhpParamsInspection */
					return (new UpdateWithJoinedData($this, $data, $joinOn, $updateFields, $dataTableName))->execute();
				}
			);

		}

		/**
		 * Registers the macros for the eloquent builder
		 */
		protected function registerEloquentBuilderMacros() {

			$cls = $this->getEloquentBuilderClass();

			$this->registerMacro(
				$cls,
				'insertModels',
				/**
				 * Executes an insert with data of the given models
				 * @param iterable|Model[] $models The models
				 * @param string[] $fields Specifies the fields to insert. If omitted, the first given model's attributes are used as field list.
				 * @param bool $withTimestamps True if to add timestamps. Else false.
				 * @return bool
				 */
				function (iterable $models, array $fields = [], bool $withTimestamps = true) {

					/** @noinspection PhpParamsInspection */
					return (new InsertModels($this, $models, $fields, $withTimestamps))->execute();
				}
			);

			$this->registerMacro(
				$cls,
				'updateWithJoinedModels',
				/**
				 * Executes an update with given data of the given models joined as virtual table
				 * @param iterable|Model[] $models The data to update
				 * @param array|array[] $joinOn Specifies the join conditions between the data table and the target table. Arrays are interpreted as multiple join conditions. Simple string item will join data based on the given field in both tables.
				 * @param Expression[]|string[]|mixed $updateFields The fields to update. Items with numeric indices are interpreted as field names. For other items the item value (or expression) is assigned to the field specified by the item key.
				 * @param bool $withTimestamps True if to update timestamps. Else false.
				 * @param string $dataTableName The data table alias
				 * @return int The number of affected rows
				 */
				function ($models, array $joinOn = [], $updateFields = [], bool $withTimestamps = true, string $dataTableName = 'data') {

					/** @noinspection PhpParamsInspection */
					return (new UpdateWithJoinedModels($this, $models, $joinOn, $updateFields, $withTimestamps, $dataTableName))->execute();
				}
			);
		}

		/**
		 * Registers a macro for the given class
		 * @param string $class The class
		 * @param string $name The macro name
		 * @param callable $callback The callback
		 */
		protected function registerMacro($class, string $name, $callback) {

			forward_static_call([$class, 'macro'], $name, $callback);

		}
	}