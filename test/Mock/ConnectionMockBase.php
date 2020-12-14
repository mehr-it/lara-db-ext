<?php


	namespace MehrItLaraDbExtTest\Mock;


	use Closure;
	use Illuminate\Database\ConnectionInterface;

	class ConnectionMockBase implements ConnectionInterface
	{

		/** @var ConnectionInterface */
		protected $base;

		/**
		 * ConnectionMock constructor.
		 * @param ConnectionInterface $base
		 */
		public function __construct(ConnectionInterface $base) {
			$this->base = $base;
		}

		/**
		 * Begin a fluent query against a database table.
		 *
		 * @param \Closure|\Illuminate\Database\Query\Builder|string $table
		 * @param string|null $as
		 * @return \Illuminate\Database\Query\Builder
		 */
		public function table($table, $as = null) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Get a new raw query expression.
		 *
		 * @param mixed $value
		 * @return \Illuminate\Database\Query\Expression
		 */
		public function raw($value) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run a select statement and return a single result.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @param bool $useReadPdo
		 * @return mixed
		 */
		public function selectOne($query, $bindings = [], $useReadPdo = true) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run a select statement against the database.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @param bool $useReadPdo
		 * @return array
		 */
		public function select($query, $bindings = [], $useReadPdo = true) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run a select statement against the database and returns a generator.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @param bool $useReadPdo
		 * @return \Generator
		 */
		public function cursor($query, $bindings = [], $useReadPdo = true) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run an insert statement against the database.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @return bool
		 */
		public function insert($query, $bindings = []) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run an update statement against the database.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @return int
		 */
		public function update($query, $bindings = []) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run a delete statement against the database.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @return int
		 */
		public function delete($query, $bindings = []) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Execute an SQL statement and return the boolean result.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @return bool
		 */
		public function statement($query, $bindings = []) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run an SQL statement and get the number of rows affected.
		 *
		 * @param string $query
		 * @param array $bindings
		 * @return int
		 */
		public function affectingStatement($query, $bindings = []) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Run a raw, unprepared query against the PDO connection.
		 *
		 * @param string $query
		 * @return bool
		 */
		public function unprepared($query) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Prepare the query bindings for execution.
		 *
		 * @param array $bindings
		 * @return array
		 */
		public function prepareBindings(array $bindings) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Execute a Closure within a transaction.
		 *
		 * @param \Closure $callback
		 * @param int $attempts
		 * @return mixed
		 *
		 * @throws \Throwable
		 */
		public function transaction(Closure $callback, $attempts = 1) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Start a new database transaction.
		 *
		 * @return void
		 */
		public function beginTransaction() {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Commit the active database transaction.
		 *
		 * @return void
		 */
		public function commit() {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Rollback the active database transaction.
		 *
		 * @return void
		 */
		public function rollBack() {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Get the number of active transactions.
		 *
		 * @return int
		 */
		public function transactionLevel() {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Execute the given callback in "dry run" mode.
		 *
		 * @param \Closure $callback
		 * @return array
		 */
		public function pretend(Closure $callback) {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * Get the name of the connected database.
		 *
		 * @return string
		 */
		public function getDatabaseName() {
			return call_user_func_array([$this->base, __FUNCTION__], func_get_args());
		}

		/**
		 * is triggered when invoking inaccessible methods in an object context.
		 *
		 * @param string $name
		 * @param array $arguments
		 * @return mixed
		 * @link https://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
		 */
		public function __call($name, $arguments) {
			return call_user_func_array([$this->base, $name], $arguments);
		}




		public function getConfig($config) {
			return null;
		}

	}