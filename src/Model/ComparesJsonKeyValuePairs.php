<?php


	namespace MehrIt\LaraDbExt\Model;


	use Illuminate\Support\Arr;
	use Illuminate\Support\Collection;

	trait ComparesJsonKeyValuePairs
	{

		/**
		 * Determine if the new and old values for a given key are equivalent.
		 *
		 * @param string $key
		 * @return bool
		 */
		public function originalIsEquivalent($key) {
			if (!array_key_exists($key, $this->original)) {
				return false;
			}
			
			if ($this->hasCast($key, ['array', 'json', 'collection', 'encrypted:array', 'encrypted:collection', 'encrypted:json'])) {
				$attribute = Arr::get($this->attributes, $key);
				$original  = Arr::get($this->original, $key);

				if ($attribute === $original)
					return true;
				
				$castedAttribute = $this->castAttribute($key, $attribute);
				$castedOriginal =  $this->castAttribute($key, $original);
				
				if ($castedAttribute instanceof Collection)
					$castedAttribute = $castedAttribute->all();
				if ($castedOriginal instanceof Collection)
					$castedOriginal = $castedOriginal->all();
				
				
				if (is_array($castedOriginal) && is_array($castedAttribute))
					return $this->arrayKeyValuesAreSame($castedAttribute, $castedOriginal);
				else
					return $castedAttribute === $castedOriginal;
			}
			
			return parent::originalIsEquivalent($key);
		}

		/**
		 * Compares the key value pairs of two arrays and returns if both arrays contain exactly the same value pairs. 
		 * @param array $a The first array
		 * @param array $b The second array
		 * @return bool True if equal. Else false.
		 */
		protected function arrayKeyValuesAreSame(array &$a, array &$b): bool {
			
			
			foreach($a as $key => &$aValue) {
				
				if (!array_key_exists($key, $b))
					return false;
				
				$bValue = &$b[$key];

				// convert collection to array for comparison
				if ($aValue instanceof Collection)
					$aValue = $aValue->all();
				if ($bValue instanceof Collection)
					$bValue = $bValue->all();
				
				if (is_array($aValue) && is_array($bValue)) {
					// compare arrays recursively
					
					if (!$this->arrayKeyValuesAreSame($aValue, $bValue))
						return false;
				}
				else if ($aValue !== $bValue) {
					// use strict comparison if not comparing two arrays
					
					return false;
				}
					
			}
			
			foreach(array_keys($b) as $currKey) {
				if (!array_key_exists($currKey, $a))
					return false;
			}
			
			return true;
		}
		
	}