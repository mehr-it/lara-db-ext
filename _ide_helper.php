<?php

	// @formatter:off

	/**
	 * A helper file for Laravel to provide autocomplete information to your IDE
	 *
	 * This file should not be included in your code, only analyzed by your IDE!
	 */

	namespace Illuminate\Database\Eloquent {

		use Generator;
		use Illuminate\Database\Query\Expression;

		class Builder {

			/**
			 * Executes an insert with data of the given models
			 * @param iterable|Model[] $models The models
			 * @param string[] $fields Specifies the fields to insert. If omitted, the first given model's attributes are used as field list.
			 * @param bool $withTimestamps True if to add timestamps. Else false.
			 * @return bool
			 */
			function insertModels(iterable $models, array $fields = [], bool $withTimestamps = true) {

			}

			/**
			 * Executes an update with given data of the given models joined as virtual table
			 * @param iterable|Model[] $models The data to update
			 * @param array|array[] $joinOn Specifies the join conditions between the data table and the target table. Arrays are interpreted as multiple join conditions. Simple string item will join data based on the given field in both tables.
			 * @param Expression[]|string[]|mixed $updateFields The fields to update. Items with numeric indices are interpreted as field names. For other items the item value (or expression) is assigned to the field specified by the item key.
			 * @param bool $withTimestamps True if to update timestamps. Else false.
			 * @param string $dataTableName The data table alias
			 * @return int The number of affected rows
			 */
			public function updateWithJoinedModels($models, array $joinOn = [], $updateFields = [], bool $withTimestamps = true, string $dataTableName = 'data') {

			}

			/**
			 * Returns a generator which queries results in chunks internally
			 * @param int $queryChunkSize The query chunk size
			 * @param callable|null $chunkProcessorCallback An optional callback to process each data chunk before yielding the items. The callback must return an iterable with the chunk items.
			 * @return Generator|Model[] The generator yielding all queried items
			 */
			public function generateChunked(int $queryChunkSize = 500, callable $chunkProcessorCallback = null) {

			}
		}
	}

	namespace Illuminate\Database\Query {

		use Generator;

		class Builder {

			/**
			 * Executes an update with given data joined as virtual table
			 * @param iterable[] $data The data to update
			 * @param array|array[] $joinOn Specifies the join conditions between the data table and the target table. Arrays are interpreted as multiple join conditions. Simple string item will join data based on the given field in both tables.
			 * @param string $dataTableName The data table alias
			 * @param Expression[]|string[]|mixed $updateFields The fields to update. Items with numeric indices are interpreted as field names. For other items the item value (or expression) is assigned to the field specified by the item key.
			 * @return int The number of affected rows
			 */
			public function updateWithJoinedData($data, array $joinOn = ['id'], $updateFields = [], string $dataTableName = 'data') {

			}

			/**
			 * Returns a generator which queries results in chunks internally
			 * @param int $queryChunkSize The query chunk size
			 * @param callable|null $chunkProcessorCallback An optional callback to process each data chunk before yielding the items. The callback must return an iterable with the chunk items.
			 * @return Generator|\stdClass[] The generator yielding all queried items
			 */
			public function generateChunked (int $queryChunkSize = 500, callable $chunkProcessorCallback = null) {

			}
		}
	}