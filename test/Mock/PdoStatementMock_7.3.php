<?php


	namespace MehrItLaraDbExtTest\Mock;


	class PdoStatementMock extends \PDOStatement
	{
		protected $columns;

		/**
		 * PHP 5 allows developers to declare constructor methods for classes.
		 * Classes which have a constructor method call this method on each newly-created object,
		 * so it is suitable for any initialization that the object may need before it is used.
		 *
		 * Note: Parent constructors are not called implicitly if the child class defines a constructor.
		 * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
		 *
		 * param [ mixed $args [, $... ]]
		 * @link https://php.net/manual/en/language.oop5.decon.php
		 */
		public function __construct(array $columns) {
			$this->columns = $columns;
		}


		/**
		 * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.2.0)<br/>
		 * Returns the number of columns in the result set
		 * @link https://php.net/manual/en/pdostatement.columncount.php
		 * @return int the number of columns in the result set represented by the
		 * PDOStatement object. If there is no result set,
		 * <b>PDOStatement::columnCount</b> returns 0.
		 */
		public function columnCount() {
			return count($this->columns);
		}

		/**
		 * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.2.0)<br/>
		 * Returns metadata for a column in a result set
		 * @link https://php.net/manual/en/pdostatement.getcolumnmeta.php
		 * @param int $column <p>
		 * The 0-indexed column in the result set.
		 * </p>
		 * @return array|false an associative array containing the following values representing
		 * the metadata for a single column:
		 * </p>
		 * <table>
		 * Column metadata
		 * <tr valign="top">
		 * <td>Name</td>
		 * <td>Value</td>
		 * </tr>
		 * <tr valign="top">
		 * <td>native_type</td>
		 * <td>The PHP native type used to represent the column value.</td>
		 * </tr>
		 * <tr valign="top">
		 * <td>driver:decl_type</td>
		 * <td>The SQL type used to represent the column value in the database.
		 * If the column in the result set is the result of a function, this value
		 * is not returned by <b>PDOStatement::getColumnMeta</b>.
		 * </td>
		 * </tr>
		 * <tr valign="top">
		 * <td>flags</td>
		 * <td>Any flags set for this column.</td>
		 * </tr>
		 * <tr valign="top">
		 * <td>name</td>
		 * <td>The name of this column as returned by the database.</td>
		 * </tr>
		 * <tr valign="top">
		 * <td>table</td>
		 * <td>The name of this column's table as returned by the database.</td>
		 * </tr>
		 * <tr valign="top">
		 * <td>len</td>
		 * <td>The length of this column. Normally -1 for
		 * types other than floating point decimals.</td>
		 * </tr>
		 * <tr valign="top">
		 * <td>precision</td>
		 * <td>The numeric precision of this column. Normally
		 * 0 for types other than floating point
		 * decimals.</td>
		 * </tr>
		 * <tr valign="top">
		 * <td>pdo_type</td>
		 * <td>The type of this column as represented by the
		 * PDO::PARAM_* constants.</td>
		 * </tr>
		 * </table>
		 * <p>
		 * Returns <b>FALSE</b> if the requested column does not exist in the result set,
		 * or if no result set exists.
		 */
		public function getColumnMeta($column) {

			return [
				'name' => $this->columns[$column]
			];
		}

		/**
		 * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.2.0)<br/>
		 * Set the default fetch mode for this statement
		 * @link https://php.net/manual/en/pdostatement.setfetchmode.php
		 * @param int $mode <p>
		 * The fetch mode must be one of the PDO::FETCH_* constants.
		 * </p>
		 * @param string|object $classNameObject [optional] <p>
		 * Class name or object
		 * </p>
		 * @param array $ctorarfg [optional] <p> Constructor arguments. </p>
		 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
		 */
		public function setFetchMode($mode, $params = null) {

		}


	}