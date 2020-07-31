<?php


	namespace MehrIt\LaraDbExt\Query;


	use Illuminate\Contracts\Events\Dispatcher;
	use Illuminate\Database\Events\StatementPrepared;

	class QueryManager
	{

		protected $preparedListeners = [];

		/** @var Dispatcher */
		protected $events;

		/**
		 * Creates a new instance
		 * @param Dispatcher $events
		 */
		public function __construct(Dispatcher $events) {
			$this->events = $events;

			$events->listen(StatementPrepared::class, function (StatementPrepared $ev) {
				foreach ($this->preparedListeners as &$curr) {
					call_user_func($curr['fn'], $ev->statement, $ev->connection);
				}

				// remove on time watcher
				$this->preparedListeners = array_filter($this->preparedListeners, function ($watcher) {
					return !$watcher['once'];
				});
			});
		}

		/**
		 * Adds a callback to StatementPrepared event
		 * @param callable $callback The callback
		 * @param bool $once True if to invoke only once
		 * @return $this
		 */
		public function onPrepared(callable $callback, $once = true) {
			$this->preparedListeners[] = [
				'fn'   => $callback,
				'once' => $once,
			];

			return $this;
		}
	}