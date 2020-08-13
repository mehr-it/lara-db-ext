<?php


	namespace MehrIt\LaraDbExt\Connection;


	use Throwable;

	trait SqlMode
	{

		/**
		 * Executes the given callback while disabling the given SQL mode option
		 * @param string $modeOption The mode option
		 * @param callable $callback The callback
		 * @return mixed The callback return
		 * @throws Throwable
		 */
		public function withSqlModeDisabled(string $modeOption, callable $callback) {

			if (!preg_match('/^[\w]+$/', $modeOption))
				throw new \InvalidArgumentException("Invalid mode option \"{$modeOption}\"");

			$this->statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'{$modeOption}',''));");

			try {
				return call_user_func($callback);
			}
			finally {

				try {
					$this->statement("SET sql_mode=(SELECT CONCAT(@@sql_mode, ',{$modeOption}'));");
				}
				catch(Throwable $ex) {

					// we could not rollback, so we disconnect to not leave the connection in unexpected state
					$this->disconnect();

					throw $ex;
				}
			}

		}

	}