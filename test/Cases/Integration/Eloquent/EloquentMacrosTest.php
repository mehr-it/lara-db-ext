<?php


	namespace MehrItLaraDbExtTest\Cases\Integration\Eloquent;


	use Carbon\Carbon;
	use DB;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\TestModel;

	class EloquentMacrosTest extends TestCase
	{
		use DatabaseTransactions;

		protected function cleanTables() {
			DB::table('test_table')->delete();
		}

		protected function setUp(): void {
			parent::setUp();

			Carbon::setTestNow(Carbon::now());
		}


		public function testUpdateWithJoinedModels() {


			$nowAtInsert = Carbon::now();

			DB::table('test_table')->insert([
				'id'         => 1,
				'name'       => 'name a',
				'x'          => 11,
				'updated_at' => $nowAtInsert,
			]);
			DB::table('test_table')->insert([
				'id'         => 2,
				'name'       => 'name b',
				'x'          => 21,
				'updated_at' => $nowAtInsert,
			]);
			DB::table('test_table')->insert([
				'id'         => 3,
				'name'       => 'name c',
				'x'          => 31,
				'updated_at' => $nowAtInsert,
			]);

			$nowAtUpdate = Carbon::now()->addHour();
			Carbon::setTestNow($nowAtUpdate);

			TestModel::updateWithJoinedModels([
					TestModel::unguarded(function() {
						return new TestModel([
							'id'   => 1,
							'name' => 'name a updated',
							'x'    => 12,
						]);
					}),
					TestModel::unguarded(function() {
						return new TestModel([
							'id'   => 2,
							'name' => 'name b updated',
							'x'    => 22,
						]);
					}),
					TestModel::unguarded(function() {
						return new TestModel([
							'id'   => 4,
							'name' => 'name b updated',
							'x'    => 42,
						]);
					}),
				]);

			// updated
			$this->assertDatabaseHas('test_table', [
				'id'         => 1,
				'name'       => 'name a updated',
				'x'          => 12,
				'updated_at' => $nowAtUpdate,
			]);
			$this->assertDatabaseHas('test_table', [
				'id'         => 2,
				'name'       => 'name b updated',
				'x'          => 22,
				'updated_at' => $nowAtUpdate,
			]);

			// not updated
			$this->assertDatabaseHas('test_table', [
				'id'         => 3,
				'name'       => 'name c',
				'x'          => 31,
				'updated_at' => $nowAtInsert,
			]);

			// not inserted
			$this->assertDatabaseMissing('test_table', [
				'id' => 4,
			]);

		}

		public function testUpdateWithJoinedModels_withCustomParameters() {

			$nowAtInsert = Carbon::now();

			DB::table('test_table')->insert([
				'id'         => 11,
				'name'       => 'name a',
				'x'          => 1,
				'updated_at' => $nowAtInsert,
			]);
			DB::table('test_table')->insert([
				'id'         => 22,
				'name'       => 'name b',
				'x'          => 2,
				'updated_at' => $nowAtInsert,
			]);
			DB::table('test_table')->insert([
				'id'         => 33,
				'name'       => 'name c',
				'x'          => 3,
				'updated_at' => $nowAtInsert,
			]);

			$nowAtUpdate = Carbon::now()->addHour();
			Carbon::setTestNow($nowAtUpdate);

			TestModel::updateWithJoinedModels(
				[
					TestModel::unguarded(function () {
						return new TestModel([
							'id'   => 1,
							'name' => 'name a updated',
							'x'    => 1,
						]);
					}),
					TestModel::unguarded(function () {
						return new TestModel([
							'id'   => 2,
							'name' => 'name b updated',
							'x'    => 2,
						]);
					}),
					TestModel::unguarded(function () {
						return new TestModel([
							'id'   => 4,
							'name' => 'name b updated',
							'x'    => 42,
						]);
					}),
				],
				['x'],
				['name' => new Expression('myData.name')],
				false,
				'myData'

			);

			// updated
			$this->assertDatabaseHas('test_table', [
				'id'         => 11,
				'name'       => 'name a updated',
				'x'          => 1,
				'updated_at' => $nowAtInsert,
			]);
			$this->assertDatabaseHas('test_table', [
				'id'         => 22,
				'name'       => 'name b updated',
				'x'          => 2,
				'updated_at' => $nowAtInsert,
			]);

			// not updated
			$this->assertDatabaseHas('test_table', [
				'id'         => 33,
				'name'       => 'name c',
				'x'          => 3,
				'updated_at' => $nowAtInsert,
			]);

			// not inserted
			$this->assertDatabaseMissing('test_table', [
				'id' => 4,
			]);

		}

		public function testInsertModels() {

			TestModel::insertModels([
				TestModel::unguarded(function () {
					return new TestModel([
						'id'   => 1,
						'name' => 'name a',
						'x'    => 12,
						'dt'   => Carbon::now()->subHours(2),
					]);
				}),
				TestModel::unguarded(function () {
					return new TestModel([
						'id'   => 2,
						'name' => 'name b',
						'x'    => 22,
						'dt'   => Carbon::now()->subHours(2),
					]);
				}),
			]);

			// updated
			$this->assertDatabaseHas('test_table', [
				'id'         => 1,
				'name'       => 'name a',
				'x'          => 12,
				'dt'         => Carbon::now()->subHours(2),
				'updated_at' => Carbon::now(),
				'created_at' => Carbon::now(),
			]);
			$this->assertDatabaseHas('test_table', [
				'id'         => 2,
				'name'       => 'name b',
				'x'          => 22,
				'dt'         => Carbon::now()->subHours(2),
				'updated_at' => Carbon::now(),
				'created_at' => Carbon::now(),
			]);

		}

		public function testInsertModels_withCustomParameters() {

			TestModel::insertModels(
				[
					TestModel::unguarded(function () {
						return new TestModel([
							'id'   => 1,
							'name' => 'name a',
							'x'    => 12,
							'dt'   => Carbon::now()->subHours(2),
						]);
					}),
					TestModel::unguarded(function () {
						return new TestModel([
							'id'   => 2,
							'name' => 'name b',
							'x'    => 22,
							'dt'   => Carbon::now()->subHours(2),
						]);
					}),
				],
				['id', 'name', 'x'],
				false
			);

			// updated
			$this->assertDatabaseHas('test_table', [
				'id'         => 1,
				'name'       => 'name a',
				'x'          => 12,
				'dt'         => null,
				'updated_at' => null,
				'created_at' => null,
			]);
			$this->assertDatabaseHas('test_table', [
				'id'         => 2,
				'name'       => 'name b',
				'x'          => 22,
				'dt'         => null,
				'updated_at' => null,
				'created_at' => null,
			]);

		}
	}