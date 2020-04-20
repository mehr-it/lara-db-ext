<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Query;


	use Generator;
	use Illuminate\Database\ConnectionInterface;
	use Illuminate\Database\Query\Builder;
	use Illuminate\Database\Query\Grammars\Grammar;
	use Illuminate\Database\Query\Processors\Processor;
	use Illuminate\Support\Arr;
	use MehrIt\LaraDbExt\Query\GenerateChunked;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class GenerateChunkedTest extends TestCase
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

		public function testChunkedGenerate() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp')
				->orderBy('id');


			$connectionInterface
				->expects($this->exactly(3))
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
					],
					[
						$this->matchesExpectedSql([
							'select * from "tmp" order by "id" asc limit 2 offset 4',
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

			$retData = iterator_to_array($ret);

			$this->assertEquals([
				(object)['id' => 1],
				(object)['id' => 2],
				(object)['id' => 3],
				(object)['id' => 4],
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

			$retData = iterator_to_array($ret);

			$this->assertEquals([
				(object)['id' => 1],
				(object)['id' => 2],
				(object)['id' => 3],
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

		public function testChunkedGenerate_noOrderBy() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp');


			$connectionInterface
				->expects($this->never())
				->method('select');

			$this->expectException(\RuntimeException::class);

			iterator_to_array((new GenerateChunked($builder, 2))->execute());
		}

		public function testChunkedGenerate_withCallback() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp')
				->orderBy('id');


			$connectionInterface
				->expects($this->exactly(3))
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
					],
					[
						$this->matchesExpectedSql([
							'select * from "tmp" order by "id" asc limit 2 offset 4',
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
			$callback = function($results) use (&$callCount) {
				++$callCount;

				$ret = [];
				foreach($results as $curr) {
					$curr->c = $callCount;
					$ret[] = $curr;
				}

				return $ret;
			};


			$ret = (new GenerateChunked($builder, 2, $callback))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$retData = iterator_to_array($ret);

			$this->assertEquals([
				(object)['id' => 1, 'c' => 1],
				(object)['id' => 2, 'c' => 1],
				(object)['id' => 3, 'c' => 2],
				(object)['id' => 4, 'c' => 2],
			], $retData);
		}

		public function testChunkedGenerate_withCallbackFiltering() {


			/** @var ConnectionInterface|MockObject $connectionInterface */
			$builder = $this->getBuilder($connectionInterface)
				->from('tmp')
				->orderBy('id');


			$connectionInterface
				->expects($this->exactly(3))
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
					],
					[
						$this->matchesExpectedSql([
							'select * from "tmp" order by "id" asc limit 2 offset 4',
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


			$callback = function($results) {
				return [Arr::first($results)];
			};


			$ret = (new GenerateChunked($builder, 2, $callback))->execute();

			$this->assertInstanceOf(Generator::class, $ret);

			$retData = iterator_to_array($ret);

			$this->assertEquals([
				(object)['id' => 1],
				(object)['id' => 3],
			], $retData);
		}
	}