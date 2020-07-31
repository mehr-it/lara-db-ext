<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Model;


	use DB;
	use Illuminate\Database\Eloquent\Model;
	use MehrIt\LaraDbExt\Model\FieldExpressions;
	use MehrIt\LaraDbExt\Model\Identifiers;
	use MehrItLaraDbExtTest\Cases\TestCase;

	class FieldExpressionsTest extends TestCase
	{
		public function testSumExpr() {
			$this->assertEquals("sum(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::sumExpr("id")->getValue());
			$this->assertEquals("sum(\"test_table\".\"id\")", FieldExpressionsTestModel::sumExpr("id", false)->getValue());
			$this->assertEquals("sum(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::sumExpr("id", "myAlias")->getValue());
			$this->assertEquals("sum(1)", FieldExpressionsTestModel::sumExpr(DB::raw("1"))->getValue());
			$this->assertEquals("sum(1) as \"myAlias\"", FieldExpressionsTestModel::sumExpr(DB::raw("1"), "myAlias")->getValue());
		}

		public function testAvgExpr() {
			$this->assertEquals("avg(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::avgExpr("id")->getValue());
			$this->assertEquals("avg(\"test_table\".\"id\")", FieldExpressionsTestModel::avgExpr("id", false)->getValue());
			$this->assertEquals("avg(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::avgExpr("id", "myAlias")->getValue());
			$this->assertEquals("avg(1)", FieldExpressionsTestModel::avgExpr(DB::raw("1"))->getValue());
			$this->assertEquals("avg(1) as \"myAlias\"", FieldExpressionsTestModel::avgExpr(DB::raw("1"), "myAlias")->getValue());
		}

		public function testMinExpr() {
			$this->assertEquals("min(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::minExpr("id")->getValue());
			$this->assertEquals("min(\"test_table\".\"id\")", FieldExpressionsTestModel::minExpr("id", false)->getValue());
			$this->assertEquals("min(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::minExpr("id", "myAlias")->getValue());
			$this->assertEquals("min(1)", FieldExpressionsTestModel::minExpr(DB::raw("1"))->getValue());
			$this->assertEquals("min(1) as \"myAlias\"", FieldExpressionsTestModel::minExpr(DB::raw("1"), "myAlias")->getValue());

		}

		public function testMaxExpr() {
			$this->assertEquals("max(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::maxExpr("id")->getValue());
			$this->assertEquals("max(\"test_table\".\"id\")", FieldExpressionsTestModel::maxExpr("id", false)->getValue());
			$this->assertEquals("max(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::maxExpr("id", "myAlias")->getValue());
			$this->assertEquals("max(1)", FieldExpressionsTestModel::maxExpr(DB::raw("1"))->getValue());
			$this->assertEquals("max(1) as \"myAlias\"", FieldExpressionsTestModel::maxExpr(DB::raw("1"), "myAlias")->getValue());
		}

		public function testLowerExpr() {
			$this->assertEquals("lower(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::lowerExpr("id")->getValue());
			$this->assertEquals("lower(\"test_table\".\"id\")", FieldExpressionsTestModel::lowerExpr("id", false)->getValue());
			$this->assertEquals("lower(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::lowerExpr("id", "myAlias")->getValue());
			$this->assertEquals("lower(1)", FieldExpressionsTestModel::lowerExpr(DB::raw("1"))->getValue());
			$this->assertEquals("lower(1) as \"myAlias\"", FieldExpressionsTestModel::lowerExpr(DB::raw("1"), "myAlias")->getValue());
		}

		public function testUpperExpr() {
			$this->assertEquals("upper(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::upperExpr("id")->getValue());
			$this->assertEquals("upper(\"test_table\".\"id\")", FieldExpressionsTestModel::upperExpr("id", false)->getValue());
			$this->assertEquals("upper(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::upperExpr("id", "myAlias")->getValue());
			$this->assertEquals("upper(1)", FieldExpressionsTestModel::upperExpr(DB::raw("1"))->getValue());
			$this->assertEquals("upper(1) as \"myAlias\"", FieldExpressionsTestModel::upperExpr(DB::raw("1"), "myAlias")->getValue());
		}


		public function testCountExpr() {
			$this->assertEquals("count(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::countExpr("id")->getValue());
			$this->assertEquals("count(\"test_table\".\"id\")", FieldExpressionsTestModel::countExpr("id", false)->getValue());
			$this->assertEquals("count(\"test_table\".\"id\", \"test_table\".\"name\")", FieldExpressionsTestModel::countExpr(["id", "name"])->getValue());
			$this->assertEquals("count(\"test_table\".\"id\", \"test_table\".\"name\") as \"myAlias\"", FieldExpressionsTestModel::countExpr(["id", "name"], "myAlias")->getValue());
			$this->assertEquals("count(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::countExpr("id", "myAlias")->getValue());
			$this->assertEquals("count(1)", FieldExpressionsTestModel::countExpr(DB::raw("1"))->getValue());
			$this->assertEquals("count(1) as \"myAlias\"", FieldExpressionsTestModel::countExpr(DB::raw("1"), "myAlias")->getValue());
			$this->assertEquals("count(1, 2)", FieldExpressionsTestModel::countExpr([DB::raw("1"), DB::raw("2")])->getValue());
			$this->assertEquals("count(1, 2) as \"myAlias\"", FieldExpressionsTestModel::countExpr([DB::raw("1"), DB::raw("2")], "myAlias")->getValue());

			$this->assertEquals("count(distinct \"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::countExpr("id", null, true)->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\")", FieldExpressionsTestModel::countExpr("id", false, true)->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\", \"test_table\".\"name\")", FieldExpressionsTestModel::countExpr(["id", "name"], null, true)->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\", \"test_table\".\"name\") as \"myAlias\"", FieldExpressionsTestModel::countExpr(["id", "name"], "myAlias", true)->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::countExpr("id", "myAlias", true)->getValue());
			$this->assertEquals("count(distinct 1)", FieldExpressionsTestModel::countExpr(DB::raw("1"), null, true)->getValue());
			$this->assertEquals("count(distinct 1) as \"myAlias\"", FieldExpressionsTestModel::countExpr(DB::raw("1"), "myAlias", true)->getValue());
			$this->assertEquals("count(distinct 1, 2)", FieldExpressionsTestModel::countExpr([DB::raw("1"), DB::raw("2")], null, true)->getValue());
			$this->assertEquals("count(distinct 1, 2) as \"myAlias\"", FieldExpressionsTestModel::countExpr([DB::raw("1"), DB::raw("2")], "myAlias", true)->getValue());
		}

		public function testCountDistinctExpr() {
			$this->assertEquals("count(distinct \"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::countDistinctExpr("id")->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\")", FieldExpressionsTestModel::countDistinctExpr("id", false)->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\", \"test_table\".\"name\")", FieldExpressionsTestModel::countDistinctExpr(["id", "name"])->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\", \"test_table\".\"name\") as \"myAlias\"", FieldExpressionsTestModel::countDistinctExpr(["id", "name"], "myAlias")->getValue());
			$this->assertEquals("count(distinct \"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::countDistinctExpr("id", "myAlias")->getValue());
			$this->assertEquals("count(distinct 1)", FieldExpressionsTestModel::countDistinctExpr(DB::raw("1"))->getValue());
			$this->assertEquals("count(distinct 1) as \"myAlias\"", FieldExpressionsTestModel::countDistinctExpr(DB::raw("1"), "myAlias")->getValue());
			$this->assertEquals("count(distinct 1, 2)", FieldExpressionsTestModel::countDistinctExpr([DB::raw("1"), DB::raw("2")])->getValue());
			$this->assertEquals("count(distinct 1, 2) as \"myAlias\"", FieldExpressionsTestModel::countDistinctExpr([DB::raw("1"), DB::raw("2")], "myAlias")->getValue());
		}

		public function testCastExpr() {
			$this->assertEquals("cast(\"test_table\".\"id\" as integer) as \"id\"", FieldExpressionsTestModel::castExpr("id", "Integer")->getValue());
			$this->assertEquals("cast(\"test_table\".\"id\" as date) as \"myAlias\"", FieldExpressionsTestModel::castExpr("id", "date", "myAlias")->getValue());
			$this->assertEquals("cast(\"test_table\".\"id\" as date)", FieldExpressionsTestModel::castExpr("id", "date", false)->getValue());
			$this->assertEquals("cast(1 as integer)", FieldExpressionsTestModel::castExpr(DB::raw("1"), "integer")->getValue());
			$this->assertEquals("cast(1 as date) as \"myAlias\"", FieldExpressionsTestModel::castExpr(DB::raw("1"), "date", "myAlias")->getValue());
			$this->assertEquals("cast(\"test_table\".\"id\" as varchar(255 )) as \"id\"", FieldExpressionsTestModel::castExpr("id", "varchar(255 )")->getValue());
		}

		public function testCastExprInvalidType() {
			$this->expectException(\InvalidArgumentException::class);

			$this->assertEquals("cast(\"test_table\".\"id\" as integer) as \"id\"", FieldExpressionsTestModel::castExpr("id", "\"asd\"")->getValue());
		}

		public function testFunctionExpr() {
			$this->assertEquals("fn(\"test_table\".\"id\") as \"id\"", FieldExpressionsTestModel::functionExpr("fn", "id")->getValue());
			$this->assertEquals("fn(\"test_table\".\"id\")", FieldExpressionsTestModel::functionExpr("fn", "id", false)->getValue());
			$this->assertEquals("fn(\"test_table\".\"id\", \"test_table\".\"name\")", FieldExpressionsTestModel::functionExpr("fn", ["id", "name"])->getValue());
			$this->assertEquals("fn(\"test_table\".\"id\", \"test_table\".\"name\") as \"myAlias\"", FieldExpressionsTestModel::functionExpr("fn", ["id", "name"], "myAlias")->getValue());
			$this->assertEquals("fn(\"test_table\".\"id\") as \"myAlias\"", FieldExpressionsTestModel::functionExpr("fn", "id", "myAlias")->getValue());
			$this->assertEquals("fn(1)", FieldExpressionsTestModel::functionExpr("fn", DB::raw("1"))->getValue());
			$this->assertEquals("fn(1) as \"myAlias\"", FieldExpressionsTestModel::functionExpr("fn", DB::raw("1"), "myAlias")->getValue());
			$this->assertEquals("fn(1, 2)", FieldExpressionsTestModel::functionExpr("fn", [DB::raw("1"), DB::raw("2")])->getValue());
			$this->assertEquals("fn(1, 2) as \"myAlias\"", FieldExpressionsTestModel::functionExpr("fn", [DB::raw("1"), DB::raw("2")], "myAlias")->getValue());
			$this->assertEquals("fn(PRE 1, 2) as \"myAlias\"", FieldExpressionsTestModel::functionExpr("fn", [DB::raw("1"), DB::raw("2")], "myAlias", DB::raw("PRE"))->getValue());
			$this->assertEquals("fn(1, 2 AFT) as \"myAlias\"", FieldExpressionsTestModel::functionExpr("fn", [DB::raw("1"), DB::raw("2")], "myAlias", null, DB::raw("AFT"))->getValue());
			$this->assertEquals("fn(PRE 1, 2 AFT) as \"myAlias\"", FieldExpressionsTestModel::functionExpr("fn", [DB::raw("1"), DB::raw("2")], "myAlias", DB::raw("PRE"), DB::raw("AFT"))->getValue());
		}
	}

	class FieldExpressionsTestModel extends Model
	{
		use Identifiers;
		use FieldExpressions;

		protected $connection = 'dummyMocked';

		protected $table = 'test_table';
	}