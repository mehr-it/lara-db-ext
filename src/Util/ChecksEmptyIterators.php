<?php


	namespace MehrIt\LaraDbExt\Util;


	trait ChecksEmptyIterators
	{
		/**
		 * Checks if the given iterable is empty
		 * @param iterable $iter The iterable
		 * @return bool True if empty. Else false.
		 */
		protected function isEmptyIterable($iter): bool {

			/** @noinspection PhpUnusedLocalVariableInspection */
			foreach ($iter as $curr) {
				return false;
			}

			return true;
		}
	}