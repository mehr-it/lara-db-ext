<?php


	namespace MehrItLaraDbExtTest\Cases\Integration\Eloquent;


	use Generator;
	use Illuminate\Database\Query\Builder;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\Post;
	use MehrItLaraDbExtTest\Model\TestModel;
	use MehrItLaraDbExtTest\Model\TestModelEloquentBuilder;
	use MehrItLaraDbExtTest\Model\TestModelEloquentBuilderBelongs;
	use MehrItLaraDbExtTest\Model\TestModelEloquentBuilderBelongsBelongs;
	use MehrItLaraDbExtTest\Model\TestModelEloquentBuilderHasManyChild;
	use MehrItLaraDbExtTest\Model\TestModelEloquentBuilderHasManyRoot;
	use MehrItLaraDbExtTest\Model\TestModelEloquentBuilderHasOneChild;
	use MehrItLaraDbExtTest\Model\TestModelEloquentBuilderHasOneRoot;
	use MehrItLaraDbExtTest\Model\User;

	class BuilderTest extends TestCase
	{
		use DatabaseTransactions;

		protected function cleanTables() {
			TestModel::query()->delete();
			Post::query()->delete();
			User::query()->delete();
		}


		public function testChunkedGenerate() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();


			$ret = TestModel::query()
				->orderBy('id')
				->generateChunked(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertContainsOnlyInstancesOf(TestModel::class, $result);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m2->id, $result[1]->id);
			$this->assertEquals($m3->id, $result[2]->id);
			$this->assertEquals($m4->id, $result[3]->id);

			$this->assertCount(4, $result);
		}

		public function testChunkedGenerate_notAllChunksFull() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();


			$ret = TestModel::query()
				->orderBy('id')
				->generateChunked(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertContainsOnlyInstancesOf(TestModel::class, $result);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m2->id, $result[1]->id);
			$this->assertEquals($m3->id, $result[2]->id);

			$this->assertCount(3, $result);

		}

		public function testChunkedGenerate_emptyResult() {


			$ret = TestModel::query()
				->orderBy('id')
				->generateChunked(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertCount(0, $result);

		}

		public function testChunkedGenerate_noOrderBy() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();


			$ret = TestModel::query()
				->generateChunked(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertContainsOnlyInstancesOf(TestModel::class, $result);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m2->id, $result[1]->id);
			$this->assertEquals($m3->id, $result[2]->id);
			$this->assertEquals($m4->id, $result[3]->id);

			$this->assertCount(4, $result);

		}

		public function testChunkedGenerate_withCallback() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();


			$ret = TestModel::query()
				->orderBy('id')
				->generateChunked(2, function ($chunk) {

					$ret = [];

					foreach ($chunk as $item) {
						$ret[] = [
							'id' => 'A' . $item->id
						];
					}

					return $ret;
				});


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals(['id' => 'A' . $m1->id], $result[0]);
			$this->assertEquals(['id' => 'A' . $m2->id], $result[1]);
			$this->assertEquals(['id' => 'A' . $m3->id], $result[2]);
			$this->assertEquals(['id' => 'A' . $m4->id], $result[3]);

			$this->assertCount(4, $result);
		}

		public function testChunkedGenerate_withCallbackFiltering() {

			$m1 = factory(TestModel::class)->create();
			factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			factory(TestModel::class)->create();


			$ret = TestModel::query()
				->orderBy('id')
				->generateChunked(2, function ($chunk) {

					return [
						$chunk[0],
					];
				});


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertContainsOnlyInstancesOf(TestModel::class, $result);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m3->id, $result[1]->id);

			$this->assertCount(2, $result);
		}

		public function testBelongsTo() {
			$parent = factory(TestModelEloquentBuilderBelongsBelongs::class)->create();


			$ret = TestModelEloquentBuilderBelongsBelongs
				::withJoined('test')
				->withJoined('test.test')
				->get();

			$returnedParent = $ret[0];

			$this->assertEquals($parent->getAttributes(), $returnedParent->getAttributes());
			$this->assertInstanceOf(TestModelEloquentBuilderBelongs::class, $returnedParent->test);
			$this->assertEquals($parent->test->getAttributes(), $returnedParent->test->getAttributes());
			$this->assertInstanceOf(TestModelEloquentBuilder::class, $returnedParent->test->test);
			$this->assertEquals($parent->test->test->getAttributes(), $returnedParent->test->test->getAttributes());
		}


		public function testHasMany() {
			$root = factory(TestModelEloquentBuilderHasManyRoot::class)->create();

			$child1 = factory(TestModelEloquentBuilderHasManyChild::class)->create([
				'root_id' => $root->id,
			]);
			$child2 = factory(TestModelEloquentBuilderHasManyChild::class)->create([
				'root_id' => $root->id,
			]);

			$ret = TestModelEloquentBuilderHasManyRoot
				::withJoined('children')
				->orderByParent()
				->orderByRelated('children', 'id')
				->get();

			$returnedParent = $ret[0];
			$this->assertEquals($root->getAttributes(), $returnedParent->getAttributes());
			$this->assertInstanceOf(TestModelEloquentBuilderHasManyChild::class, $returnedParent->children->get(0));
			$this->assertInstanceOf(TestModelEloquentBuilderHasManyChild::class, $returnedParent->children->get(1));
			$this->assertEquals($child1->getAttributes(), $returnedParent->children->get(0)->getAttributes());
			$this->assertEquals($child2->getAttributes(), $returnedParent->children->get(1)->getAttributes());
		}

		public function testHasOne() {
			$root = factory(TestModelEloquentBuilderHasOneRoot::class)->create();

			$child1 = factory(TestModelEloquentBuilderHasOneChild::class)->create([
				'root_id' => $root->id,
			]);

			$ret = TestModelEloquentBuilderHasOneRoot
				::withJoined('child')
				->orderByParent()
				->get();

			$returnedParent = $ret[0];
			$this->assertEquals($root->getAttributes(), $returnedParent->getAttributes());
			$this->assertInstanceOf(TestModelEloquentBuilderHasOneChild::class, $returnedParent->child);
			$this->assertEquals($child1->getAttributes(), $returnedParent->child->getAttributes());

		}

		public function testGenerateChunked() {
			$r1 = factory(TestModel::class)->create();
			$r2 = factory(TestModel::class)->create();
			$r3 = factory(TestModel::class)->create();

			$gen = TestModel::where('id', '>', 0)->orderBy('id')->generateChunked();

			$results = [];
			foreach ($gen as $curr) {
				$results[] = $curr;
			}


			$this->assertEquals($r1->getAttributes(), array_filter($results[0]->getAttributes()));
			$this->assertEquals($r2->getAttributes(), array_filter($results[1]->getAttributes()));
			$this->assertEquals($r3->getAttributes(), array_filter($results[2]->getAttributes()));
		}

		public function testWithExpression() {

			factory(User::class)->create(['id' => 1]);
			factory(User::class)->create(['id' => 2]);
			factory(User::class)->create(['id' => 3]);


			$users = User::withExpression('ids', 'select 1 union all select 2', ['id'])
				->whereIn('id', function (Builder $query) {
					$query->from('ids');
				})->get();

			$this->assertEquals([1, 2], $users->pluck('id')->all());
		}

		public function testWithRecursiveExpression() {

			factory(User::class)->create(['id' => 1]);
			factory(User::class)->create(['id' => 2, 'parent_id' => 1]);
			factory(User::class)->create(['id' => 3, 'parent_id' => 2]);


			$query = User::query()
				->where('id', 3)
				->unionAll(
					User::query()
						->select('users.*')
						->join('parents', 'parents.parent_id', '=', 'users.id')
				);

			$users = User::from('parents')
				->withRecursiveExpression('parents', $query)
				->get();

			$this->assertEquals([3, 2, 1], $users->pluck('id')->all());
		}
	}