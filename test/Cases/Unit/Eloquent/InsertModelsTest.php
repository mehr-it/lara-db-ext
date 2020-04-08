<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Eloquent;


	use Carbon\Carbon;
	use Illuminate\Database\Connection;
	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\ConnectionResolverInterface;
	use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Query\Builder as QueryBuilder;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use MehrIt\LaraDbExt\Eloquent\InsertModels;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\TestModel;
	use MehrItLaraDbExtTest\Model\TestModelWithCustomCreatedAtField;
	use MehrItLaraDbExtTest\Model\TestModelWithCustomUpdatedAtField;
	use MehrItLaraDbExtTest\Model\TestModelWithoutCreatedAtField;
	use MehrItLaraDbExtTest\Model\TestModelWithoutTimestamps;
	use MehrItLaraDbExtTest\Model\TestModelWithoutUpdatedAtField;
	use PHPUnit\Framework\MockObject\MockObject;

	class InsertModelsTest extends TestCase
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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

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

			(new InsertModels($builder, $models))->execute();
		}


		public function testUpdateWithJoinedModels_singleRow() {

			$models = [
				TestModel::unguarded(function () {
					return new TestModel([
						'sku' => 1,
						'b'   => 2,
					]);
				}),
			];

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

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

			(new InsertModels($builder, $models))->execute();
		}

		public function testUpdateWithJoinedModels_specificFieldsOnly() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

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

			(new InsertModels($builder, $models, ['sku']))->execute();
		}

		public function testUpdateWithJoinedModels_withoutTimestamps() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

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

			(new InsertModels($builder, $models, [], false))->execute();
		}

		public function testUpdateWithJoinedModels_timestampsAlreadySetInModel() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);

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

			(new InsertModels($builder, $models))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithoutTimestamps() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithoutTimestamps());

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

			(new InsertModels($builder, $models, []))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithoutUpdatedAtField() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithoutUpdatedAtField());

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

			(new InsertModels($builder, $models))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithCustomUpdatedAtField() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithCustomUpdatedAtField());

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

			(new InsertModels($builder, $models))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithoutCreatedAtField() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithoutCreatedAtField());

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

			(new InsertModels($builder, $models))->execute();
		}

		public function testUpdateWithJoinedModels_modelWithCustomCreatedAtField() {

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

			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface, new TestModelWithCustomCreatedAtField());

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

			(new InsertModels($builder, $models))->execute();
		}
	}