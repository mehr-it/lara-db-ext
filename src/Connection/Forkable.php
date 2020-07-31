<?php


	namespace MehrIt\LaraDbExt\Connection;


	use Illuminate\Contracts\Redis\Connection;

	interface Forkable
	{
		/**
		 * Creates a new connection with same configuration as the current connection
		 * @param array $options Allows to override connection options
		 * @param array $attributes Allows to set PDO attributes
		 * @return Connection The new connection
		 */
		public function fork(array $options = [], array $attributes = []);


		/**
		 * Destroys the given connection if it is a forked connection
		 */
		public function destroyFork();
	}