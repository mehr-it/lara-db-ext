<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Eloquent;


	use Carbon\Carbon;
	use Illuminate\Database\Connection;
	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\ConnectionResolverInterface;
	use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Query\Builder as QueryBuilder;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use Illuminate\Support\Arr;
	use InvalidArgumentException;
	use MehrIt\LaraDbExt\Eloquent\UpdateWithJoinedModels;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\TestModel;
	use MehrItLaraDbExtTest\Model\TestModelWithCustomUpdatedAtField;
	use MehrItLaraDbExtTest\Model\TestModelWithMutations;
	use MehrItLaraDbExtTest\Model\TestModelWithoutPrimaryKey;
	use MehrItLaraDbExtTest\Model\TestModelWithoutTimestamps;
	use MehrItLaraDbExtTest\Model\TestModelWithoutUpdatedAtField;
	use PHPUnit\Framework\MockObject\MockObject;
	use SebastianBergmann\Comparator\ComparisonFailure;

	class UpdateWithJoinedModelsTest extends TestCase
	{
		protected $modelNowTimestampString;

		/**
		 * Gets a new query builder instance
		 * @param ConnectionInterface|MockObject|null $connectionInterface
		 * @param null|Model $model The model
		 * @return EloquentBuilder
		 */
		protected function getBuilder(&$connectionInterface = null, $model = null) {
			$grammar = new Grammar();

			/** @var Processor|MockObject $processor */
			$processor = $this->getMockBuilder(Processor::class)->getMock();

			/** @var Connection|MockObject $connectionInterface */
			$connectionInterface = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
			$connectionInterface
				->method('getQueryGrammar')
				->willReturn($grammar);

			/** @var ConnectionResolverInterface|MockObject $resolver */
			$resolver = $this->getMockBuilder(ConnectionResolverInterface::class)->getMock();
			$resolver
				->method('connection')
				->willReturn($connectionInterface);


			$builder = new EloquentBuilder(new QueryBuilder($connectionInterface, $grammar, $processor));

			$model = $model ?: new TestModel();

			// set connection resolver for model
			forward_static_call([$model, 'setConnectionResolver'], $resolver);

			$builder->setModel($model);

			return $builder;
		}

		protected function setUp(): void {
			parent::setUp();

			Carbon::setTestNow(Carbon::now());

			$this->modelNowTimestampString = (new TestModel())->freshTimestampString();
		}


		public function testUpdateWithJoinedModels() {

			$data = [
				TestModel::unguarded(function() {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModel::unguarded(function() {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku']))->execute();
		}

		public function testUpdateWithJoinedModels_joinWithCustomMapping() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."sku" = "data"."other_sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."sku" = "data"."other_sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString,
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, [['sku' => 'other_sku']]))->execute();
		}

		public function testUpdateWithJoinedModels_joinWithCustomArgs() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."sku" = lower(sku) set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."sku" = lower(sku) set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, [['test_table.sku', '=', new Expression('lower(sku)')]]))->execute();
		}

		public function testUpdateWithJoinedModels_singleRow() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString,
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku']))->execute();
		}

		public function testUpdateWithJoinedModels_onlySpecificColumns() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
						'custom' => 'a',
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
						'custom' => 'b',
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "custom", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "custom", (?) as "updated_at")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."custom" = "data"."custom"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "custom", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "custom", (?) as "updated_at") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."custom" = "data"."custom"',
			];
			$expectedBindings = [
				1, 2, 'a', $this->modelNowTimestampString, 3, 4, 'b', $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku'], ['custom']))->execute();
		}

		public function testUpdateWithJoinedModels_updateExpression() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = data.b + 1',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = data.b + 1',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku'], ['b' => new Expression('data.b + 1')]))->execute();
		}

		public function testUpdateWithJoinedModels_customJoinTableName() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "myData" on "test_table"."sku" = "myData"."sku" set "test_table"."updated_at" = "myData"."updated_at", "test_table"."b" = "myData"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "myData" on "test_table"."sku" = "myData"."sku" set "test_table"."updated_at" = "myData"."updated_at", "test_table"."b" = "myData"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku'], [], true, 'myData'))->execute();
		}


		public function testUpdateWithJoinedModels_customUpdatedAtAlreadySetInModels() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 1,
						'b'          => 2,
						'updated_at' => '2020-01-01 12:00:00',
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 3,
						'b'          => 4,
						'updated_at' => null,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, '2020-01-01 12:00:00', 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku'], ['b']))->execute();
		}

		public function testUpdateWithJoinedModels_noJoinConditionsGiven() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."id" = "data"."id" set "test_table"."updated_at" = "data"."updated_at", "test_table"."sku" = "data"."sku", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."id" = "data"."id" set "test_table"."updated_at" = "data"."updated_at", "test_table"."sku" = "data"."sku", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, []))->execute();
		}

		public function testUpdateWithJoinedModels_updateTimestampsDisabled() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4,
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku'], [], false))->execute();
		}

		public function testUpdateWithJoinedModels_dataEmpty() {

			$data = [];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$this->expectException(InvalidArgumentException::class);

			(new UpdateWithJoinedModels($builder, $data))->execute();
		}

		public function testUpdateWithJoinedModels_noModelsGiven() {

			$data = [
				['a' => 1],
				['a' => 2]
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$this->expectException(InvalidArgumentException::class);

			(new UpdateWithJoinedModels($builder, $data))->execute();
		}

		public function testUpdateWithJoinedModels_emptyModels() {

			$data = [
				new TestModel(),
				new TestModel(),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$this->expectException(InvalidArgumentException::class);

			(new UpdateWithJoinedModels($builder, $data))->execute();
		}

		public function testUpdateWithJoinedModels_dataTableEmpty() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 1,
						'b'          => 2,
						'updated_at' => '2020-01-01 12:00:00',
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 3,
						'b'          => 4,
						'updated_at' => null,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

			$this->expectException(InvalidArgumentException::class);

			(new UpdateWithJoinedModels($builder, $data, ['id'], [], true, ''))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithMutations() {

			$data = [
				TestModelWithMutations::unguarded(function () {
					return new TestModelWithMutations([
						'id'   => 1,
						'name' => 'Hans Meyer',
						'dt'   => Carbon::now(),
					]);
				}),
				TestModelWithMutations::unguarded(function () {
					return new TestModelWithMutations([
						'id'   => 3,
						'name' => 'Max Mustermann',
						'dt'   => Carbon::now(),
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithMutations());

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "id", (?) as "name", (?) as "dt", (?) as "updated_at") union all (select (?) as "id", (?) as "name", (?) as "dt", (?) as "updated_at")) as "data" on "test_table"."id" = "data"."id" set "test_table"."updated_at" = "data"."updated_at", "test_table"."name" = "data"."name", "test_table"."dt" = "data"."dt"',
				'update "test_table" inner join (select (?) as "id", (?) as "name", (?) as "dt", (?) as "updated_at" union all select (?) as "id", (?) as "name", (?) as "dt", (?) as "updated_at") as "data" on "test_table"."id" = "data"."id" set "test_table"."updated_at" = "data"."updated_at", "test_table"."name" = "data"."name", "test_table"."dt" = "data"."dt"',
			];
			$expectedBindings = [
				1, 'hans meyer', Carbon::now()->format('Y-m-d H:i:s'), $this->modelNowTimestampString, 3, 'max mustermann', Carbon::now()->format('Y-m-d H:i:s'), $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data))->execute();
		}

		public function testUpdateWithJoinedData_modelWithoutPrimaryKey() {

			$data = [
				TestModelWithoutPrimaryKey::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithoutPrimaryKey::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithoutPrimaryKey());

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "updated_at") union all (select (?) as "sku", (?) as "b", (?) as "updated_at")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "updated_at" union all select (?) as "sku", (?) as "b", (?) as "updated_at") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."updated_at" = "data"."updated_at", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku']))->execute();
		}

		public function testUpdateWithJoinedData_modelWithoutPrimaryKey_noJoinConditionsGiven() {

			$data = [
				TestModelWithoutPrimaryKey::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithoutPrimaryKey::unguarded(function () {
					return new TestModel([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithoutPrimaryKey());

			$this->expectException(InvalidArgumentException::class);

			(new UpdateWithJoinedModels($builder, $data))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithoutTimestamps() {

			$data = [
				TestModelWithoutTimestamps::unguarded(function () {
					return new TestModelWithoutTimestamps([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithoutTimestamps::unguarded(function () {
					return new TestModelWithoutTimestamps([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithoutTimestamps());

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4,
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku']))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithoutUpdatedAtField() {

			$data = [
				TestModelWithoutUpdatedAtField::unguarded(function () {
					return new TestModelWithoutUpdatedAtField([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithoutUpdatedAtField::unguarded(function () {
					return new TestModelWithoutUpdatedAtField([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithoutUpdatedAtField());

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b") union all (select (?) as "sku", (?) as "b")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b" union all select (?) as "sku", (?) as "b") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, 3, 4,
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku']))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithCustomUpdatedAtField() {

			$data = [
				TestModelWithCustomUpdatedAtField::unguarded(function () {
					return new TestModelWithCustomUpdatedAtField([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithCustomUpdatedAtField::unguarded(function () {
					return new TestModelWithCustomUpdatedAtField([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithCustomUpdatedAtField());

			$expectedSql      = [
				'update "test_table" inner join ((select (?) as "sku", (?) as "b", (?) as "upd_field") union all (select (?) as "sku", (?) as "b", (?) as "upd_field")) as "data" on "test_table"."sku" = "data"."sku" set "test_table"."upd_field" = "data"."upd_field", "test_table"."b" = "data"."b"',
				'update "test_table" inner join (select (?) as "sku", (?) as "b", (?) as "upd_field" union all select (?) as "sku", (?) as "b", (?) as "upd_field") as "data" on "test_table"."sku" = "data"."sku" set "test_table"."upd_field" = "data"."upd_field", "test_table"."b" = "data"."b"',
			];
			$expectedBindings = [
				1, 2, $this->modelNowTimestampString, 3, 4, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('update')
				->with(
					$this->matchesExpectedSql($expectedSql),
					$expectedBindings
				);

			(new UpdateWithJoinedModels($builder, $data, ['sku']))->execute();
		}

	}