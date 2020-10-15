<?php


	namespace MehrItLaraDbExtTest\Cases\Integration\Query;


	use DateTime;
	use Generator;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use Illuminate\Support\Facades\DB;
	use MehrIt\LaraDbExt\Query\Builder;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\Post;
	use MehrItLaraDbExtTest\Model\TestModel;
	use MehrItLaraDbExtTest\Model\User;
	use RuntimeException;

	class BuilderTest extends TestCase
	{
		use DatabaseTransactions;

		protected function cleanTables() {
			TestModel::query()->delete();
			User::query()->delete();
			Post::query()->delete();
		}

		protected function createBuilder() {
			return new Builder(DB::connection());
		}


		public function testSelectEmpty() {

			$builder = $this->createBuilder();
			$result  = $builder
				->selectPrefixed('test_table.*', 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals([], $result->toArray());
		}

		public function testSelectPrefixed() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->selectPrefixed('test_table.*', 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'myPfx__id'         => $m1->id,
				'myPfx__name'       => $m1->name,
				'myPfx__x'          => $m1->x,
				'myPfx__dt'         => $m1->dt,
				'myPfx__created_at' => $m1->created_at,
				'myPfx__updated_at' => $m1->updated_at,
			], $result->first());
		}

		public function testSelectPrefixed_multipleColumns() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->selectPrefixed(['id', 'name'], 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'myPfx__id'   => $m1->id,
				'myPfx__name' => $m1->name,
			], $result->first());
		}


		public function testSelectPrefixed_alias() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->selectPrefixed(['id as myID', 'name'], 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'myPfx__myID' => $m1->id,
				'myPfx__name' => $m1->name,
			], $result->first());
		}


		public function testSelectPrefixed_expression_withAlias() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->selectPrefixed(['id', new Expression('lower(name) as lName')], 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'myPfx__id'    => $m1->id,
				'myPfx__lName' => strtolower($m1->name),
			], $result->first());
		}

		public function testSelectPrefixed_multiplePrefixes() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->selectPrefixed([
					'pfx1_' => 'id',
					'pfx2_' => ['name', 'x'],
					'pfx3_' => 'x as myX',
				])
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'pfx1_id'   => $m1->id,
				'pfx2_name' => $m1->name,
				'pfx2_x'    => $m1->x,
				'pfx3_myX'  => $m1->x,
			], $result->first());
		}

		public function testSelectPrefixed_specialChars() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->selectPrefixed(['id'], 'my.')
				->addSelectPrefixed(['id'], 'my:')
				->addSelectPrefixed(['id'], 'my.1')
				->addSelectPrefixed(['id'], 'my\\1')
				->addSelectPrefixed(['id'], 'my\\')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'my.id'  => $m1->id,
				'my:id'  => $m1->id,
				'my.1id' => $m1->id,
				'my\\1id' => $m1->id,
				'my\\id' => $m1->id,
			], $result->first());
		}

		public function testAddSelectPrefixed() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->select('*')
				->addSelectPrefixed('id', 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'id'         => $m1->id,
				'name'       => $m1->name,
				'x'          => $m1->x,
				'dt'         => $m1->dt,
				'created_at' => $m1->created_at,
				'updated_at' => $m1->updated_at,
				'myPfx__id'  => $m1->id,
			], $result->first());
		}

		public function testAddSelectPrefixed_multipleColumns() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->select('*')
				->addSelectPrefixed(['id', 'name'], 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'id'          => $m1->id,
				'name'        => $m1->name,
				'x'           => $m1->x,
				'dt'          => $m1->dt,
				'created_at'  => $m1->created_at,
				'updated_at'  => $m1->updated_at,
				'myPfx__id'   => $m1->id,
				'myPfx__name' => $m1->name,
			], $result->first());
		}

		public function testAddSelectPrefixed_alias() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->select('*')
				->addSelectPrefixed(['id as myID', 'name'], 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'id'          => $m1->id,
				'name'        => $m1->name,
				'x'           => $m1->x,
				'dt'          => $m1->dt,
				'created_at'  => $m1->created_at,
				'updated_at'  => $m1->updated_at,
				'myPfx__myID' => $m1->id,
				'myPfx__name' => $m1->name,
			], $result->first());
		}

		public function testAddSelectPrefixed_expression_withAlias() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->select('*')
				->addSelectPrefixed(['id', new Expression('lower(name) as lName')], 'myPfx__')
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'id'           => $m1->id,
				'name'         => $m1->name,
				'x'            => $m1->x,
				'dt'           => $m1->dt,
				'created_at'   => $m1->created_at,
				'updated_at'   => $m1->updated_at,
				'myPfx__id'    => $m1->id,
				'myPfx__lName' => strtolower($m1->name),
			], $result->first());
		}

		public function testAddSelectPrefixed_multiplePrefixes() {
			$m1 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();
			$result  = $builder
				->select('*')
				->addSelectPrefixed([
					'pfx1_' => 'id',
					'pfx2_' => ['name', 'x'],
					'pfx3_' => 'x as myX',
				])
				->from('test_table')
				->get();

			$this->assertEquals((object)[
				'id'         => $m1->id,
				'name'       => $m1->name,
				'x'          => $m1->x,
				'dt'         => $m1->dt,
				'created_at' => $m1->created_at,
				'updated_at' => $m1->updated_at,
				'pfx1_id'    => $m1->id,
				'pfx2_name'  => $m1->name,
				'pfx2_x'     => $m1->x,
				'pfx3_myX'   => $m1->x,
			], $result->first());
		}

		public function testChunkedGenerate() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunked(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

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

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunked(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m2->id, $result[1]->id);
			$this->assertEquals($m3->id, $result[2]->id);

			$this->assertCount(3, $result);

		}

		public function testChunkedGenerate_emptyResult() {


			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunked(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertCount(0, $result);

		}

		public function testChunkedGenerate_noOrderBy() {


			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->generateChunked(2);


			$this->expectException(RuntimeException::class);

			iterator_to_array($ret);


		}

		public function testChunkedGenerate_withCallback() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunked(2, function($chunk) {

					$ret = [];

					foreach($chunk as $item) {
						$ret[] = (object)[
							'id' => 'A' . $item->id
						];
					}

					return $ret;
				});


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals('A' . $m1->id, $result[0]->id);
			$this->assertEquals('A' . $m2->id, $result[1]->id);
			$this->assertEquals('A' . $m3->id, $result[2]->id);
			$this->assertEquals('A' . $m4->id, $result[3]->id);

			$this->assertCount(4, $result);
		}

		public function testChunkedGenerate_withCallbackFiltering() {

			$m1 = factory(TestModel::class)->create();
			factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunked(2, function($chunk) {

					return [
						$chunk[0],
					];
				});


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m3->id, $result[1]->id);

			$this->assertCount(2, $result);
		}

		public function testChunkedGenerateById() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->generateChunkedById(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m2->id, $result[1]->id);
			$this->assertEquals($m3->id, $result[2]->id);
			$this->assertEquals($m4->id, $result[3]->id);

			$this->assertCount(4, $result);
		}

		public function testChunkedGenerateById_withAlias() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->select('id as test')
				->from('test_table')
				->generateChunkedById(2, 'id', 'test');


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals($m1->id, $result[0]->test);
			$this->assertEquals($m2->id, $result[1]->test);
			$this->assertEquals($m3->id, $result[2]->test);
			$this->assertEquals($m4->id, $result[3]->test);

			$this->assertCount(4, $result);
		}

		public function testChunkedGenerateById_withCustomColumn() {

			$m1 = factory(TestModel::class)->create(['x' => 'd']);
			$m2 = factory(TestModel::class)->create(['x' => 'c']);
			$m3 = factory(TestModel::class)->create(['x' => 'b']);
			$m4 = factory(TestModel::class)->create(['x' => 'a']);

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->generateChunkedById(2, 'x');


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals($m4->x, $result[0]->x);
			$this->assertEquals($m3->x, $result[1]->x);
			$this->assertEquals($m2->x, $result[2]->x);
			$this->assertEquals($m1->x, $result[3]->x);

			$this->assertCount(4, $result);
		}

		public function testChunkedGenerateById_notAllChunksFull() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunkedById(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m2->id, $result[1]->id);
			$this->assertEquals($m3->id, $result[2]->id);

			$this->assertCount(3, $result);

		}

		public function testChunkedGenerateById_emptyResult() {


			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunkedById(2);


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertCount(0, $result);

		}

		public function testChunkedGenerateById_withCallback() {

			$m1 = factory(TestModel::class)->create();
			$m2 = factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			$m4 = factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunkedById(2, 'id', null, function($chunk) {

					$ret = [];

					foreach($chunk as $item) {
						$ret[] = (object)[
							'id' => 'A' . $item->id
						];
					}

					return $ret;
				});


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals('A' . $m1->id, $result[0]->id);
			$this->assertEquals('A' . $m2->id, $result[1]->id);
			$this->assertEquals('A' . $m3->id, $result[2]->id);
			$this->assertEquals('A' . $m4->id, $result[3]->id);

			$this->assertCount(4, $result);
		}

		public function testChunkedGenerateById_withCallbackFiltering() {

			$m1 = factory(TestModel::class)->create();
			factory(TestModel::class)->create();
			$m3 = factory(TestModel::class)->create();
			factory(TestModel::class)->create();

			$builder = $this->createBuilder();

			$ret = $builder
				->from('test_table')
				->orderBy('id')
				->generateChunkedById(2, 'id',null, function($chunk) {

					return [
						$chunk[0],
					];
				});


			$this->assertInstanceOf(Generator::class, $ret);

			$result = iterator_to_array($ret);

			$this->assertEquals($m1->id, $result[0]->id);
			$this->assertEquals($m3->id, $result[1]->id);

			$this->assertCount(2, $result);
		}


		public function testWithExpression() {

			factory(User::class)->create(['id' => 1]);
			factory(User::class)->create(['id' => 2, 'parent_id' => 1]);
			factory(User::class)->create(['id' => 3, 'parent_id' => 2]);

			factory(Post::class)->create(['id' => 1, 'user_id' => 1]);
			factory(Post::class)->create(['id' => 2, 'user_id' => 2]);

			$rows = DB::table('u')
				->select('u.id')
				->withExpression('u', DB::table('users'))
				->withExpression('p', function (Builder $query) {
					$query->from('posts');
				})
				->join('p', 'p.user_id', '=', 'u.id')
				->get();

			$this->assertEquals([1, 2], $rows->pluck('id')->all());
		}

		public function testWithRecursiveExpression() {
			$query = 'select 1 union all select number + 1 from numbers where number < 3';

			$rows = DB::table('numbers')
				->withRecursiveExpression('numbers', $query, ['number'])
				->get();

			$this->assertEquals([1, 2, 3], $rows->pluck('number')->all());
		}

		public function testInsertUsing() {

			factory(User::class)->create(['id' => 1]);
			factory(User::class)->create(['id' => 2, 'parent_id' => 1]);
			factory(User::class)->create(['id' => 3, 'parent_id' => 2]);

			factory(Post::class)->create(['id' => 1, 'user_id' => 1]);
			factory(Post::class)->create(['id' => 2, 'user_id' => 2]);

			DB::table('posts')
				->withExpression('u', DB::table('users')->select('id')->where('id', '>', 1))
				->insertUsing(['user_id'], DB::table('u'));

			$this->assertEquals([1, 2, 2, 3], DB::table('posts')->pluck('user_id')->all());
		}

		public function testUpdate() {

			factory(User::class)->create(['id' => 1]);
			factory(User::class)->create(['id' => 2, 'parent_id' => 1]);
			factory(User::class)->create(['id' => 3, 'parent_id' => 2]);

			factory(Post::class)->create(['id' => 1, 'user_id' => 1]);
			factory(Post::class)->create(['id' => 2, 'user_id' => 2]);

			DB::table('posts')
				->withExpression('u', DB::table('users')->where('id', '>', 1))
				->update([
					'user_id'    => DB::raw('(select min(id) from u)'),
					'updated_at' => new DateTime(),
				]);

			$this->assertEquals([2, 2], DB::table('posts')->pluck('user_id')->all());
		}

		public function testDelete() {

			factory(User::class)->create(['id' => 1]);
			factory(User::class)->create(['id' => 2, 'parent_id' => 1]);
			factory(User::class)->create(['id' => 3, 'parent_id' => 2]);

			factory(Post::class)->create(['id' => 1, 'user_id' => 1]);
			factory(Post::class)->create(['id' => 2, 'user_id' => 2]);

			DB::table('posts')
				->withExpression('u', DB::table('users')->where('id', '>', 1))
				->whereIn('user_id', DB::table('u')->select('id'))
				->delete();

			$this->assertEquals([1], DB::table('posts')->pluck('user_id')->all());
		}


	}
