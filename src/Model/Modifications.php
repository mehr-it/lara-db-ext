<?php


	namespace MehrIt\LaraDbExt\Model;

	trait Modifications
	{
		/**
		 * Gets the modifications made to the model data
		 * @return mixed[] The modified fields. Field name as key. Array with original and current value as items.
		 */
		public function getModifications(): array {
			$modified = $this->getDirty();

			$ret = [];
			foreach ($modified as $field => $currentValue) {
				$originalValue = $this->getOriginal($field);

				$ret[$field] = [$originalValue, $currentValue];
			}

			return $ret;
		}
	}