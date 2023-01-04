<?php


	namespace MehrIt\LaraDbExt\Query\Concerns;


	use Illuminate\Contracts\Support\Arrayable;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Support\Arr;
	use Traversable;

	trait InsertOnDuplicateKey
	{

		/**
		 * Insert using ON DUPLICATE KEY UPDATE
		 * @param Traversable|Arrayable|array|array[] $values The data to insert
		 * @param array $updateColumns The columns to update. For numeric keys the value is interpreted as column name and the column is updated with the corresponding field from the dat array. For associative keys, the key is used as column name and the value can be a constant value or an expression which is used to update the column.
		 * @return bool
		 */
		public function insertOnDuplicateKey($values, array $updateColumns = []) {

			if (is_callable([$this, 'applyBeforeQueryCallbacks']))
				$this->applyBeforeQueryCallbacks();
			
			if ($values instanceof Arrayable)
				$values = $values->toArray();
			elseif ($values instanceof Traversable)
				$values = iterator_to_array($values);

			$values = array_filter($values);

			if (empty($values))
				return true;

			// Here, we will sort the insert keys for every record so that each insert is
			// in the same order for the record. We need to make sure this is the case
			// so there are not any errors or problems when inserting these records.
			foreach ($values as $key => &$value) {
				ksort($value);
			}

			/** @var Grammar $grammar */
			$grammar = $this->getGrammar();


			// retrieve columns from first row
			$columns = [];
			foreach (Arr::first($values) as $field => $v) {
				$columns[] = $field;
			}

			// build values SQL
			$valuesSql = implode(', ', array_map(
				function ($row) use ($grammar) {
					return "({$grammar->parameterize($row)})";
				},
				$values
			));


			if (!$updateColumns)
				$updateColumns = $columns;


			// build update SQL
			$onUpdateBindings = [];
			$updateSql        = $this->onDuplicateKeyUpdateValues($updateColumns, $onUpdateBindings);


			/** @noinspection PhpParamsInspection */
			return $this->getConnection()->insert(
				$grammar->compileInsertUsing($this, $columns, "values {$valuesSql} on duplicate key update {$updateSql}"),
				array_merge(
					$this->cleanBindings(Arr::flatten($values, 1)),
					$onUpdateBindings
				)
			);

		}

		/**
		 * Generates the onUpdate SQL
		 * @param array $fields The fields. Numeric keys will be interpreted as input value mapping
		 * @param array $bindings
		 * @return string The onUpdate string
		 */
		protected function onDuplicateKeyUpdateValues($fields, array &$bindings = []) {

			/** @var Grammar $grammar */
			$grammar = $this->getGrammar();

			$values = [];

			foreach ($fields as $key => $value) {

				if (is_numeric($key)) {
					$wrappedName = $grammar->wrap($value);

					$values[] = "{$wrappedName} = values({$wrappedName})";
				}
				else {
					$wrappedName = $grammar->wrap($key);

					if ($value instanceof Expression) {
						$values[] = "{$wrappedName} = " . $grammar->getValue($value);
					}
					else {
						$values[]   = "{$wrappedName} = ?";
						$bindings[] = $value;
					}

				}

			}

			return implode(', ', $values);
		}

	}