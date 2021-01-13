<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Query;


	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use InvalidArgumentException;
	use MehrIt\LaraDbExt\Query\Builder;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class BuilderTest extends TestCase
	{
		/**
		 * Gets a new query builder instance
		 * @param ConnectionInterface|MockObject|null $connectionInterface
		 * @return Builder
		 */
		protected function getBuilder(&$connectionInterface = null) {
			$grammar = new Grammar();

			$processor = new Processor();

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$connectionInterface = $this->getMockBuilder(ConnectionInterface::class)->getMock();

			return new Builder($connectionInterface, $grammar, $processor);
		}


		public function testWhere_valuesArray_2args() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('name', [
					'name A',
					'name B'
				]);
			$this->assertEquals('select * from "test_table" where "name" in (?, ?)', $builder->toSql());
			$this->assertSame(['name A', 'name B'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}



		public function testWhere_valuesArray_equal() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('name', '=', [
					'name A',
					'name B'
				]);
			$this->assertEquals('select * from "test_table" where "name" in (?, ?)', $builder->toSql());
			$this->assertSame(['name A', 'name B'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhere_valuesArray_notEqual() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('name', '!=', [
					'name A',
					'name B'
				]);
			$this->assertEquals('select * from "test_table" where "name" not in (?, ?)', $builder->toSql());
			$this->assertSame(['name A', 'name B'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhere_valuesArray_greaterLower() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('name', '<>', [
					'name A',
					'name B'
				]);
			$this->assertEquals('select * from "test_table" where "name" not in (?, ?)', $builder->toSql());
			$this->assertSame(['name A', 'name B'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhere_valuesArray_multipleColumns_2args() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where(['name', 'x'], [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where ("name", "x") in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame(['name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhere_valuesArray_multipleColumns_equal() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where(['name', 'x'], '=', [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where ("name", "x") in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame(['name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhere_valuesArray_multipleColumns_notEqual() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where(['name', 'x'], '!=', [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where ("name", "x") not in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame(['name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhere_valuesArray_multipleColumns_greaterLower() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where(['name', 'x'], '<>', [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where ("name", "x") not in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame(['name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhereIn_multipleColumns() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->whereIn(['name', 'x'], [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where ("name", "x") in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame(['name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}


		public function testWhereIn_multipleColumns_subSelect() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->whereIn(['name', 'x'], function ($query) {
					return $query->select(['name', 'x'])
						->from('test_table')
						->where('name', 'name A');
				});
			$this->assertEquals('select * from "test_table" where ("name", "x") in (select "name", "x" from "test_table" where "name" = ?)', $builder->toSql());
			$this->assertSame(['name A'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhereMultiIn() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->whereMultiIn(['name', 'x'], [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where "x" = ? and ("name", "x") in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame([5, 'name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testOrWhereMultiIn() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->orWhereMultiIn(['name', 'x'], [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where "x" = ? or ("name", "x") in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame([5, 'name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhereMultiNotIn() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->whereMultiNotIn(['name', 'x'], [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where "x" = ? and ("name", "x") not in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame([5, 'name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testOrOrWhereMultiNotIn() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->orWhereMultiNotIn(['name', 'x'], [
					['name A', '2'],
					['name B', '4'],
				]);
			$this->assertEquals('select * from "test_table" where "x" = ? or ("name", "x") not in ((?, ?), (?, ?))', $builder->toSql());
			$this->assertSame([5, 'name A', '2', 'name B', '4'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhereMultipleColumns_2Args() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->whereMultipleColumns(['name', 'x'], ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where "x" = ? and ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);
		}

		public function testWhereMultipleColumns_equal() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->whereMultipleColumns(['name', 'x'], '=', ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where "x" = ? and ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);

		}

		public function testWhereMultipleColumns_Not() {
			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->whereMultipleColumns(['name', 'x'], '!=', ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where "x" = ? and not ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);
		}

		public function testOrWhereMultipleColumns_2Args() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->orWhereMultipleColumns(['name', 'x'], ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where "x" = ? or ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);
		}

		public function testOrWhereMultipleColumns_equal() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->orWhereMultipleColumns(['name', 'x'], '=', ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where "x" = ? or ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);

		}

		public function testOrWhereMultipleColumns_Not() {
			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('x', 5)
				->orWhereMultipleColumns(['name', 'x'], '!=', ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where "x" = ? or not ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);
		}

		public function testWhereColumn_multipleColumns_2Args() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->whereColumn(['name', 'x'], ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);
		}

		public function testWhereColumn_multipleColumns_equal() {

			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->whereColumn(['name', 'x'], '=', ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);

		}

		public function testWhereColumn_multipleColumns_Not() {
			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->whereColumn(['name', 'x'], '!=', ['c1', 'c2']);
			$this->assertEquals('select * from "test_table" where not ("name" = "c1" and "x" = "c2")', $builder->toSql());
			$this->assertSame($builder, $self);
		}

		public function testWhereNotNested() {
			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->whereNotNested(function ($query) {
					return $query->where('name', 'hans');
				});
			$this->assertEquals('select * from "test_table" where not ("name" = ?)', $builder->toSql());
			$this->assertSame(['hans'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testWhereNotNestedAnd() {
			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('name', '!=', 'peter')
				->whereNotNested(function ($query) {
					return $query->where('name', 'hans');
				});
			$this->assertEquals('select * from "test_table" where "name" != ? and not ("name" = ?)', $builder->toSql());
			$this->assertSame(['peter', 'hans'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testOrWhereNotNestedOr() {
			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('name', 'peter')
				->whereNotNested(function ($query) {
					return $query->where('name', 'hans');
				}, 'or');
			$this->assertEquals('select * from "test_table" where "name" = ? or not ("name" = ?)', $builder->toSql());
			$this->assertSame(['peter', 'hans'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testOrWhereNotNested() {
			$builder = $this->getBuilder();
			$self    = $builder
				->select('*')
				->from('test_table')
				->where('name', 'peter')
				->orWhereNotNested(function ($query) {
					return $query->where('name', 'hans');
				});
			$this->assertEquals('select * from "test_table" where "name" = ? or not ("name" = ?)', $builder->toSql());
			$this->assertSame(['peter', 'hans'], $builder->getBindings());
			$this->assertSame($builder, $self);
		}

		public function testUpdateWithJoinedData() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'update "tmp" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "tmp"."sku" = "data"."sku" set "tmp"."b" = "data"."b"',
				'update "tmp" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, ['sku']);
		}

		public function testUpdateWithJoinedData_containsNullValues() {

			$data = [
				[
					'sku' => 1,
					'a'   => null,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'a' => null,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'update "tmp" inner join ((select (?) as "sku", (?) as "a", (?) as "b") union all (select (?) as "sku", (?) as "a", (?) as "b")) as "data" on "tmp"."sku" = "data"."sku" set "tmp"."a" = "data"."a", "tmp"."b" = "data"."b"',
				'update "tmp" inner join (select (?) as "sku", (?) as "a", (?) as "b" union all select (?) as "sku", (?) as "a", (?) as "b") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."a" = "data"."a", "tmp"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, null, 2, 3, null, 4
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, ['sku']);
		}

		public function testUpdateWithJoinedData_containsArrays() {

			$data = [
				[
					'sku' => 1,
					'a'   => ['x' => 19],
					'b'   => 2,
				],
				[
					'sku' => 3,
					'a' => ['x' => 18],
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');


			$connectionInterface
				->expects($this->never())
				->method('update');

			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessageMatches('/.*?Data fields must not contain arrays.*/');

			$builder->updateWithJoinedData($data, ['sku']);
		}

		public function testUpdateWithJoinedData_containsEmptyArrays() {

			$data = [
				[
					'sku' => 1,
					'a'   => [],
					'b'   => 2,
				],
				[
					'sku' => 3,
					'a' => [],
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$connectionInterface
				->expects($this->never())
				->method('update');

			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessageMatches('/.*?Data fields must not contain arrays.*/');

			$builder->updateWithJoinedData($data, ['sku']);
		}

		public function testUpdateWithJoinedData_joinWithCustomMapping() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'update "tmp" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "tmp"."sku" = "data"."other_sku" set "tmp"."b" = "data"."b"',
				'update "tmp" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = "data"."other_sku" set "tmp"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, [['sku' => 'other_sku']]);

		}

		public function testUpdateWithJoinedData_joinWithCustomArgs() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'update "tmp" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "tmp"."sku" = lower(sku) set "tmp"."b" = "data"."b"',
				'update "tmp" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = lower(sku) set "tmp"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, [['tmp.sku', '=', new Expression('lower(sku)')]]);
		}

		public function testUpdateWithJoinedData_singleRow() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = 'update "tmp" inner join (select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."b" = "data"."b"';
			$expectedBindings = [
				1, 2,
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$expectedSql,
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, ['sku']);
		}

		public function testUpdateWithJoinedData_onlySpecificColumns() {

			$data = [
				[
					'sku'    => 1,
					'b'      => 2,
					'custom' => 'a',
				],
				[
					'sku'    => 3,
					'b'      => 4,
					'custom' => 'b',
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'update "tmp" inner join ((select (?) as "sku", (?) as "b", (?) as "custom") union all (select (?) as "sku", (?) as "b", (?) as "custom")) as "data" on "tmp"."sku" = "data"."sku" set "tmp"."custom" = "data"."custom"',
				'update "tmp" inner join (select (?) as "sku", (?) as "b", (?) as "custom" union all select (?) as "sku", (?) as "b", (?) as "custom") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."custom" = "data"."custom"',
			];
			$expectedBindings = [
				1, 2, 'a', 3, 4, 'b'
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, ['sku'], ['custom']);
		}

		public function testUpdateWithJoinedData_updateExpression() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'update "tmp" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "tmp"."sku" = "data"."sku" set "tmp"."b" = data.b + 1',
				'update "tmp" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."b" = data.b + 1',
			];
			$expectedBindings = [
				1, 2, 3, 4
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, ['sku'], ['b' => new Expression('data.b + 1')]);
		}

		public function testUpdateWithJoinedData_customJoinTableName() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'update "tmp" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "myData" on "tmp"."sku" = "myData"."sku" set "tmp"."b" = "myData"."b"',
				'update "tmp" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "myData" on "tmp"."sku" = "myData"."sku" set "tmp"."b" = "myData"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->updateWithJoinedData($data, ['sku'], [], 'myData');
		}


		public function testUpdateWithJoinedData_dataEmpty() {

			$data = [];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$connectionInterface
				->expects($this->never())
				->method('update');

			$builder->updateWithJoinedData($data);
		}

		public function testUpdateWithJoinedData_dataContainsOnlyEmptyRows() {

			$data = [
				[],
				[]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$connectionInterface
				->expects($this->never())
				->method('update');

			$builder->updateWithJoinedData($data);
		}

		public function testUpdateWithJoinedData_noJoinConditionsGiven() {

			$data = [
				['a' => 1],
				['a' => 1]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$this->expectException(InvalidArgumentException::class);

			$connectionInterface
				->expects($this->never())
				->method('update');

			$builder->updateWithJoinedData($data, []);
		}

		public function testUpdateWithJoinedData_dataTableEmpty() {

			$data = [
				['a' => 1],
				['a' => 1]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$this->expectException(InvalidArgumentException::class);

			$connectionInterface
				->expects($this->never())
				->method('update');

			$builder->updateWithJoinedData($data, ['id'], [], '');
		}

		public function testInsertOnDuplicateKey() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'insert into "tmp" ("b", "sku") values (?, ?), (?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku")',
			];
			$expectedBindings = [
				2, 1, 4, 3
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->insertOnDuplicateKey($data);
		}

		public function testInsertOnDuplicateKey_singleRow() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'insert into "tmp" ("b", "sku") values (?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku")',
			];
			$expectedBindings = [
				2, 1,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->insertOnDuplicateKey($data);
		}

		public function testInsertOnDuplicateKey_onlySpecificUpdateFields() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'insert into "tmp" ("b", "sku") values (?, ?), (?, ?) on duplicate key update "sku" = values("sku")',
			];
			$expectedBindings = [
				2, 1, 4, 3
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->insertOnDuplicateKey($data, ['sku']);
		}

		public function testInsertOnDuplicateKey_customUpdateValue() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'insert into "tmp" ("b", "sku") values (?, ?), (?, ?) on duplicate key update "sku" = values("sku"), "b" = ?',
			];
			$expectedBindings = [
				2, 1, 4, 3, 99
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->insertOnDuplicateKey($data, ['sku', 'b' => 99]);
		}

		public function testInsertOnDuplicateKey_updateFieldsWithExpression() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
				],
				[
					'sku' => 3,
					'b'   => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql      = [
				'insert into "tmp" ("b", "sku") values (?, ?), (?, ?) on duplicate key update "sku" = values("sku"), "b" = now()',
			];
			$expectedBindings = [
				2, 1, 4, 3
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			$builder->insertOnDuplicateKey($data, ['sku', 'b' => new Expression('now()')]);
		}

		public function testInsertOnDuplicateKey_dataEmpty() {

			$data = [

			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$connectionInterface
				->expects($this->never())
				->method('insert');

			$builder->insertOnDuplicateKey($data);
		}

		public function testInsertOnDuplicateKey_dataContainsOnlyEmptyRows() {

			$data = [
				[],
				[],
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$connectionInterface
				->expects($this->never())
				->method('insert');

			$builder->insertOnDuplicateKey($data);
		}

	}