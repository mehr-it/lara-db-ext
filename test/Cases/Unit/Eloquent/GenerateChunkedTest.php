<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Eloquent;


	use Carbon\Carbon;
	use Generator;
	use Illuminate\Database\Connection;
	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\ConnectionResolverInterface;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use Illuminate\Database\Query\Builder as QueryBuilder;
	use Illuminate\Support\Arr;
	use MehrIt\LaraDbExt\Eloquent\GenerateChunked;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\TestModel;
	use PHPUnit\Framework\MockObject\MockObject;

	class GenerateChunkedTest extends TestCase
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

			$processor = new Processor();

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

		public function testChunkedGenerate() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);


			$connectionInterface
				->expects($this->exactly(3))
				->method('select')
				->withConsecutive(
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 0',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 2',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 4',
						]),
						[]
					]
				)
				->willReturnOnConsecutiveCalls(
					[
						(object)['id' => 1],
						(object)['id' => 2],
					],
					[
						(object)['id' => 3],
						(object)['id' => 4],
					],
					[]
				);

			$ret = (new GenerateChunked($builder, 2))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$this->assertInstanceOf(Generator::class, $ret);

			$retArr = iterator_to_array($ret);
			$this->assertContainsOnlyInstancesOf(TestModel::class, $retArr);

			$retData = [];
			foreach ($retArr as $curr) {
				$retData[] = $curr->toArray();
			}

			$this->assertEquals([
				['id' => 1],
				['id' => 2],
				['id' => 3],
				['id' => 4],
			], $retData);
		}


		public function testChunkedGenerate_noFullChunkReturned() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp')
				->orderBy('id');


			$connectionInterface
				->expects($this->exactly(2))
				->method('select')
				->withConsecutive(
					[
						$this->matchesExpectedSql([
							'select * from "tmp" order by "id" asc limit 2 offset 0',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "tmp" order by "id" asc limit 2 offset 2',
						]),
						[]
					]
				)
				->willReturnOnConsecutiveCalls(
					[
						(object)['id' => 1],
						(object)['id' => 2],
					],
					[
						(object)['id' => 3],
					],
					[]
				);

			$ret = (new GenerateChunked($builder, 2))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$retArr = iterator_to_array($ret);
			$this->assertContainsOnlyInstancesOf(TestModel::class, $retArr);

			$retData = [];
			foreach($retArr as $curr) {
				$retData[] = $curr->toArray();
			}

			$this->assertEquals([
				['id' => 1],
				['id' => 2],
				['id' => 3],
			], $retData);
		}

		public function testChunkedGenerate_resultEmpty() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp')
				->orderBy('id');


			$connectionInterface
				->expects($this->exactly(1))
				->method('select')
				->withConsecutive(
					[
						$this->matchesExpectedSql([
							'select * from "tmp" order by "id" asc limit 2 offset 0',
						]),
						[]
					]
				)
				->willReturnOnConsecutiveCalls(
					[]
				);

			$ret = (new GenerateChunked($builder, 2))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$retData = iterator_to_array($ret);

			$this->assertEquals([], $retData);
		}

		public function testChunkedGenerate_customOrderBy() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->orderBy('test_table.name');


			$connectionInterface
				->expects($this->exactly(3))
				->method('select')
				->withConsecutive(
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."name" asc limit 2 offset 0',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."name" asc limit 2 offset 2',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."name" asc limit 2 offset 4',
						]),
						[]
					]
				)
				->willReturnOnConsecutiveCalls(
					[
						(object)['id' => 1],
						(object)['id' => 2],
					],
					[
						(object)['id' => 3],
						(object)['id' => 4],
					],
					[]
				);

			$ret = (new GenerateChunked($builder, 2))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$this->assertInstanceOf(Generator::class, $ret);

			$retArr = iterator_to_array($ret);
			$this->assertContainsOnlyInstancesOf(TestModel::class, $retArr);

			$retData = [];
			foreach ($retArr as $curr) {
				$retData[] = $curr->toArray();
			}

			$this->assertEquals([
				['id' => 1],
				['id' => 2],
				['id' => 3],
				['id' => 4],
			], $retData);
		}

		public function testChunkedGenerate_withCallback() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);


			$connectionInterface
				->expects($this->exactly(3))
				->method('select')
				->withConsecutive(
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 0',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 2',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 4',
						]),
						[]
					]
				)
				->willReturnOnConsecutiveCalls(
					[
						(object)['id' => 1],
						(object)['id' => 2],
					],
					[
						(object)['id' => 3],
						(object)['id' => 4],
					],
					[]
				);


			$callCount = 0;
			$callback  = function ($results) use (&$callCount) {
				++$callCount;

				$ret = [];
				foreach ($results as $curr) {
					$curr->c = $callCount;
					$ret[]   = $curr;
				}

				return $ret;
			};

			$ret = (new GenerateChunked($builder, 2, $callback))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$retArr = iterator_to_array($ret);
			$this->assertContainsOnlyInstancesOf(TestModel::class, $retArr);

			$retData = [];
			foreach ($retArr as $curr) {
				$retData[] = $curr->toArray();
			}

			$this->assertEquals([
				['id' => 1, 'c' => 1],
				['id' => 2, 'c' => 1],
				['id' => 3, 'c' => 2],
				['id' => 4, 'c' => 2],
			], $retData);
		}

		public function testChunkedGenerate_withCallbackFiltering() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface);


			$connectionInterface
				->expects($this->exactly(3))
				->method('select')
				->withConsecutive(
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 0',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 2',
						]),
						[]
					],
					[
						$this->matchesExpectedSql([
							'select * from "test_table" order by "test_table"."id" asc limit 2 offset 4',
						]),
						[]
					]
				)
				->willReturnOnConsecutiveCalls(
					[
						(object)['id' => 1],
						(object)['id' => 2],
					],
					[
						(object)['id' => 3],
						(object)['id' => 4],
					],
					[]
				);


			$callback = function ($results) {
				return [Arr::first($results)];
			};

			$ret = (new GenerateChunked($builder, 2, $callback))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$retArr = iterator_to_array($ret);
			$this->assertContainsOnlyInstancesOf(TestModel::class, $retArr);

			$retData = [];
			foreach ($retArr as $curr) {
				$retData[] = $curr->toArray();
			}

			$this->assertEquals([
				['id' => 1],
				['id' => 3],
			], $retData);
		}
	}