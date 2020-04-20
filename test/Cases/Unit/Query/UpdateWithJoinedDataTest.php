<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Query;


	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\Query\Builder;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use InvalidArgumentException;
	use MehrIt\LaraDbExt\Query\UpdateWithJoinedData;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class UpdateWithJoinedDataTest extends TestCase
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

		public function testUpdateWithJoinedData() {

			$data = [
				[
					'sku' => 1,
					'b' => 2,
				],
				[
					'sku' => 3,
					'b' => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql = [
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

			(new UpdateWithJoinedData($builder, $data, ['sku']))->execute();
		}

		public function testUpdateWithJoinedData_joinWithCustomMapping() {

			$data = [
				[
					'sku' => 1,
					'b' => 2,
				],
				[
					'sku' => 3,
					'b' => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql = [
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

			(new UpdateWithJoinedData($builder, $data, [['sku' => 'other_sku']]))->execute();
		}

		public function testUpdateWithJoinedData_joinWithCustomArgs() {

			$data = [
				[
					'sku' => 1,
					'b' => 2,
				],
				[
					'sku' => 3,
					'b' => 4,
				]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql = [
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

			(new UpdateWithJoinedData($builder, $data, [['tmp.sku', '=', new Expression('lower(sku)')]]))->execute();
		}

		public function testUpdateWithJoinedData_singleRow() {

			$data = [
				[
					'sku' => 1,
					'b' => 2,
				],
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$expectedSql = 'update "tmp" inner join (select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."b" = "data"."b"';
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

			(new UpdateWithJoinedData($builder, $data, ['sku']))->execute();
		}

		public function testUpdateWithJoinedData_onlySpecificColumns() {

			$data = [
				[
					'sku' => 1,
					'b'   => 2,
					'custom' => 'a',
				],
				[
					'sku' => 3,
					'b'   => 4,
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

			(new UpdateWithJoinedData($builder, $data, ['sku'], ['custom']))->execute();
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

			(new UpdateWithJoinedData($builder, $data, ['sku'], ['b' => new Expression('data.b + 1')]))->execute();
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

			(new UpdateWithJoinedData($builder, $data, ['sku'], [], 'myData'))->execute();
		}

		public function testUpdateWithJoinedData_withDefaultFields_noUpdateFieldsPassed() {

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
				'update "tmp" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "tmp"."sku" = "data"."sku" set "tmp"."def" = ?, "tmp"."b" = "data"."b"',
				'update "tmp" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."def" = ?, "tmp"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4, -100
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedData($builder, $data, ['sku']))
				->setUpdateDefaultFields(['def' => -100])
				->execute();
		}

		public function testUpdateWithJoinedData_withDefaultFields_updateFieldsPassed() {

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
				'update "tmp" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "tmp"."sku" = "data"."sku" set "tmp"."def" = ?, "tmp"."b" = "data"."b"',
				'update "tmp" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "tmp"."sku" = "data"."sku" set "tmp"."def" = ?, "tmp"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4, -100
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedData($builder, $data, ['sku'], ['b']))
				->setUpdateDefaultFields(['def' => -100])
				->execute();
		}

		public function testUpdateWithJoinedData_withDefaultFieldsOverriddenByUpdateFields() {

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

			(new UpdateWithJoinedData($builder, $data, ['sku'], ['b']))
				->setUpdateDefaultFields(['b' => -100])
				->execute();
		}

		public function testUpdateWithJoinedData_dataEmpty() {

			$data = [];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$this->expectException(InvalidArgumentException::class);

			(new UpdateWithJoinedData($builder, $data))->execute();
		}

		public function testUpdateWithJoinedData_dataContainsOnlyEmptyRows() {

			$data = [
				[],
				[]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');

			$this->expectException(InvalidArgumentException::class);

			(new UpdateWithJoinedData($builder, $data))->execute();
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

			(new UpdateWithJoinedData($builder, $data, []))->execute();
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

			(new UpdateWithJoinedData($builder, $data, ['id'], [], ''))->execute();
		}
	}