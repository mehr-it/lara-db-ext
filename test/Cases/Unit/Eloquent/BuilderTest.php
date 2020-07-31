<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Eloquent;


	use Carbon\Carbon;
	use Illuminate\Database\Connection;
	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\ConnectionResolverInterface;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Database\Query\Grammars\Grammar;
	use InvalidArgumentException;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\TestModel;
	use MehrItLaraDbExtTest\Model\TestModelWithCustomCreatedAtField;
	use MehrItLaraDbExtTest\Model\TestModelWithCustomUpdatedAtField;
	use MehrItLaraDbExtTest\Model\TestModelWithMutations;
	use MehrItLaraDbExtTest\Model\TestModelWithoutCreatedAtField;
	use MehrItLaraDbExtTest\Model\TestModelWithoutPrimaryKey;
	use MehrItLaraDbExtTest\Model\TestModelWithoutTimestamps;
	use MehrItLaraDbExtTest\Model\TestModelWithoutUpdatedAtField;
	use PHPUnit\Framework\MockObject\MockObject;

	class BuilderTest extends TestCase
	{

		protected $modelNowTimestampString;

		/**
		 * Gets a new query builder instance
		 * @param ConnectionInterface|MockObject|null $connectionInterface
		 * @param null|Model $model The model
		 */
		protected function mockDbConnection(&$connectionInterface = null, $model = null) {
			$grammar = new Grammar();

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


			$model = $model ?: new TestModel();

			// set connection resolver for model
			forward_static_call([$model, 'setConnectionResolver'], $resolver);
		}

		protected function setUp(): void {
			parent::setUp();

			Carbon::setTestNow(Carbon::now());

			$this->modelNowTimestampString = (new TestModel())->freshTimestampString();
		}


		public function testInsertModels() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "updated_at") values (?, ?, ?, ?), (?, ?, ?, ?)';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModels($models);
		}


		public function testInsertModels_singleRow() {

			$models = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "updated_at") values (?, ?, ?, ?)';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModels($models);
		}

		public function testInsertModels_specificFieldsOnly() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("created_at", "sku", "updated_at") values (?, ?, ?), (?, ?, ?)';
			$expectedBindings = [
				$this->modelNowTimestampString, 1, $this->modelNowTimestampString, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModels($models, ['sku']);
		}

		public function testInsertModels_withoutTimestamps() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "sku") values (?, ?), (?, ?)';
			$expectedBindings = [
				2, 1, 4, 3,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModels($models, [], false);
		}

		public function testInsertModels_timestampsAlreadySetInModel() {

			$models = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 1,
						'b'          => 2,
						'created_at' => '2020-01-01 12:00:00',
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 3,
						'b'          => 4,
						'updated_at' => '2020-01-01 12:00:00',
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "updated_at") values (?, ?, ?, ?), (?, ?, ?, ?)';
			$expectedBindings = [
				2, '2020-01-01 12:00:00', 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, '2020-01-01 12:00:00'
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);


			TestModel::insertModels($models);
		}

		public function testInsertModels_modelWithoutTimestamps() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "sku") values (?, ?), (?, ?)';
			$expectedBindings = [
				2, 1, 4, 3,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithoutTimestamps::insertModels($models);
		}

		public function testInsertModels_modelWithoutUpdatedAtField() {

			$models = [
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

			$this->mockDbConnection($connectionInterface, new TestModelWithoutUpdatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku") values (?, ?, ?), (?, ?, ?)';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, 4, $this->modelNowTimestampString, 3,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithoutUpdatedAtField::insertModels($models);
		}

		public function testInsertModels_modelWithCustomUpdatedAtField() {

			$models = [
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

			$this->mockDbConnection($connectionInterface, new TestModelWithCustomUpdatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "upd_field") values (?, ?, ?, ?), (?, ?, ?, ?)';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithCustomUpdatedAtField::insertModels($models);
		}

		public function testInsertModels_modelWithoutCreatedAtField() {

			$models = [
				TestModelWithoutCreatedAtField::unguarded(function () {
					return new TestModelWithoutCreatedAtField([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithCustomCreatedAtField::unguarded(function () {
					return new TestModelWithoutCreatedAtField([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface, new TestModelWithoutCreatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "sku", "updated_at") values (?, ?, ?), (?, ?, ?)';
			$expectedBindings = [
				2, 1, $this->modelNowTimestampString, 4, 3, $this->modelNowTimestampString,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithoutCreatedAtField::insertModels($models);
		}

		public function testInsertModels_modelWithCustomCreatedAtField() {

			$models = [
				TestModelWithCustomCreatedAtField::unguarded(function () {
					return new TestModelWithCustomCreatedAtField([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithCustomCreatedAtField::unguarded(function () {
					return new TestModelWithCustomCreatedAtField([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface, new TestModelWithCustomCreatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "cre_field", "sku", "updated_at") values (?, ?, ?, ?), (?, ?, ?, ?)';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithCustomCreatedAtField::insertModels($models);
		}


		public function testUpdateWithJoinedModels() {

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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, ['sku']);
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

			$this->mockDbConnection($connectionInterface);

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
			
			TestModel::updateWithJoinedModels($data, [['sku' => 'other_sku']]);
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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, [['test_table.sku', '=', new Expression('lower(sku)')]]);
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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, ['sku']);
		}

		public function testUpdateWithJoinedModels_onlySpecificColumns() {

			$data = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'    => 1,
						'b'      => 2,
						'custom' => 'a',
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'    => 3,
						'b'      => 4,
						'custom' => 'b',
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, ['sku'], ['custom']);
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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, ['sku'], ['b' => new Expression('data.b + 1')]);
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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, ['sku'], [], true, 'myData');
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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, ['sku'], ['b']);
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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data);
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

			$this->mockDbConnection($connectionInterface);

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

			TestModel::updateWithJoinedModels($data, ['sku'], [], false);
		}

		public function testUpdateWithJoinedModels_dataEmpty() {

			$data = [];

			$this->mockDbConnection($connectionInterface);

			$connectionInterface
				->expects($this->never())
				->method('update');

			TestModel::updateWithJoinedModels($data);
		}

		public function testUpdateWithJoinedModels_noModelsGiven() {

			$data = [
				['a' => 1],
				['a' => 2]
			];

			$this->mockDbConnection($connectionInterface);

			$this->expectException(InvalidArgumentException::class);

			TestModel::updateWithJoinedModels($data);
		}

		public function testUpdateWithJoinedModels_emptyModels() {

			$data = [
				new TestModel(),
				new TestModel(),
			];

			$this->mockDbConnection($connectionInterface);

			$connectionInterface
				->expects($this->never())
				->method('update');

			TestModel::updateWithJoinedModels($data);
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

			$this->mockDbConnection($connectionInterface);

			$this->expectException(InvalidArgumentException::class);

			TestModel::updateWithJoinedModels($data, ['id'], [], true, '');
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

			$this->mockDbConnection($connectionInterface, new TestModelWithMutations());

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

			TestModelWithMutations::updateWithJoinedModels($data);
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

			$this->mockDbConnection($connectionInterface, new TestModelWithoutPrimaryKey());

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

			TestModelWithoutPrimaryKey::updateWithJoinedModels($data, ['sku']);
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

			$this->mockDbConnection($connectionInterface, new TestModelWithoutPrimaryKey());

			$this->expectException(InvalidArgumentException::class);

			TestModelWithoutPrimaryKey::updateWithJoinedModels($data);
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

			$this->mockDbConnection($connectionInterface, new TestModelWithoutTimestamps());

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

			TestModelWithoutTimestamps::updateWithJoinedModels($data, ['sku']);
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

			$this->mockDbConnection($connectionInterface, new TestModelWithoutUpdatedAtField());

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

			TestModelWithoutUpdatedAtField::updateWithJoinedModels($data, ['sku']);
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

			$this->mockDbConnection($connectionInterface, new TestModelWithCustomUpdatedAtField());

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

			TestModelWithCustomUpdatedAtField::updateWithJoinedModels($data, ['sku']);
		}


		public function testInsertModelsOnDuplicateKey() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "updated_at") values (?, ?, ?, ?), (?, ?, ?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku"), "updated_at" = values("updated_at")';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModelsOnDuplicateKey($models);
		}


		public function testInsertModelsOnDuplicateKey_singleRow() {

			$models = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "updated_at") values (?, ?, ?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku"), "updated_at" = values("updated_at")';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModelsOnDuplicateKey($models);
		}

		public function testInsertModelsOnDuplicateKey_specificUpdateFieldsOnly() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "updated_at") values (?, ?, ?, ?), (?, ?, ?, ?) on duplicate key update "sku" = values("sku")';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModelsOnDuplicateKey($models, ['sku']);
		}

		public function testInsertModelsOnDuplicateKey_withoutTimestamps() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "sku") values (?, ?), (?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku")';
			$expectedBindings = [
				2, 1, 4, 3,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModel::insertModelsOnDuplicateKey($models, [], false);
		}

		public function testInsertModelsOnDuplicateKey_timestampsAlreadySetInModel() {

			$models = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 1,
						'b'          => 2,
						'created_at' => '2020-01-01 12:00:00',
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'sku'        => 3,
						'b'          => 4,
						'updated_at' => '2020-01-01 12:00:00',
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "updated_at") values (?, ?, ?, ?), (?, ?, ?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku"), "updated_at" = values("updated_at")';
			$expectedBindings = [
				2, '2020-01-01 12:00:00', 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, '2020-01-01 12:00:00'
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);


			TestModel::insertModelsOnDuplicateKey($models);
		}

		public function testInsertModelsOnDuplicateKey_modelWithoutTimestamps() {

			$models = [
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

			$this->mockDbConnection($connectionInterface);

			$expectedSql      = 'insert into "test_table" ("b", "sku") values (?, ?), (?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku")';
			$expectedBindings = [
				2, 1, 4, 3,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithoutTimestamps::insertModelsOnDuplicateKey($models);
		}

		public function testInsertModelsOnDuplicateKey_modelWithoutUpdatedAtField() {

			$models = [
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

			$this->mockDbConnection($connectionInterface, new TestModelWithoutUpdatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku") values (?, ?, ?), (?, ?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku")';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, 4, $this->modelNowTimestampString, 3,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithoutUpdatedAtField::insertModelsOnDuplicateKey($models);
		}

		public function testInsertModelsOnDuplicateKey_modelWithCustomUpdatedAtField() {

			$models = [
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

			$this->mockDbConnection($connectionInterface, new TestModelWithCustomUpdatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "created_at", "sku", "upd_field") values (?, ?, ?, ?), (?, ?, ?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku"), "upd_field" = values("upd_field")';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithCustomUpdatedAtField::insertModelsOnDuplicateKey($models);
		}

		public function testInsertModelsOnDuplicateKey_modelWithoutCreatedAtField() {

			$models = [
				TestModelWithoutCreatedAtField::unguarded(function () {
					return new TestModelWithoutCreatedAtField([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithCustomCreatedAtField::unguarded(function () {
					return new TestModelWithoutCreatedAtField([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface, new TestModelWithoutCreatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "sku", "updated_at") values (?, ?, ?), (?, ?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku"), "updated_at" = values("updated_at")';
			$expectedBindings = [
				2, 1, $this->modelNowTimestampString, 4, 3, $this->modelNowTimestampString,
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithoutCreatedAtField::insertModelsOnDuplicateKey($models);
		}

		public function testInsertModelsOnDuplicateKey_modelWithCustomCreatedAtField() {

			$models = [
				TestModelWithCustomCreatedAtField::unguarded(function () {
					return new TestModelWithCustomCreatedAtField([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
				TestModelWithCustomCreatedAtField::unguarded(function () {
					return new TestModelWithCustomCreatedAtField([
						'sku' => 3,
						'b'   => 4,
					]);
				}),
			];

			$this->mockDbConnection($connectionInterface, new TestModelWithCustomCreatedAtField());

			$expectedSql      = 'insert into "test_table" ("b", "cre_field", "sku", "updated_at") values (?, ?, ?, ?), (?, ?, ?, ?) on duplicate key update "b" = values("b"), "sku" = values("sku"), "updated_at" = values("updated_at")';
			$expectedBindings = [
				2, $this->modelNowTimestampString, 1, $this->modelNowTimestampString, 4, $this->modelNowTimestampString, 3, $this->modelNowTimestampString
			];

			$connectionInterface
				->expects($this->once())
				->method('insert')
				->with(
					$expectedSql,
					$expectedBindings
				);

			TestModelWithCustomCreatedAtField::insertModelsOnDuplicateKey($models);
		}
		
	}