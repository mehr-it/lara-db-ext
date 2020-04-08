<?php


	namespace MehrItLaraDbExtTest\Cases\Integration\Query;


	use DB;
	use Illuminate\Database\Query\Expression;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrItLaraDbExtTest\Cases\TestCase;

	class QueryMacrosTest extends TestCase
	{
		use DatabaseTransactions;

		protected function cleanTables() {
			DB::table('test_table')->delete();
		}


		public function testUpdateWithJoinedData() {

			DB::table('test_table')->insert([
				'id' => 1,
				'name' => 'name a',
				'x' => 11
			]);
			DB::table('test_table')->insert([
				'id' => 2,
				'name' => 'name b',
				'x' => 21
			]);
			DB::table('test_table')->insert([
				'id' => 3,
				'name' => 'name c',
				'x' => 31
			]);

			DB::table('test_table')
				->updateWithJoinedData([
					[
						'id'   => 1,
						'name' => 'name a updated',
						'x'    => 12,
					],
					[
						'id'   => 2,
						'name' => 'name b updated',
						'x'    => 22,
					],
					[
						'id'   => 4,
						'name' => 'name b updated',
						'x'    => 42,
					],
				]);

			// updated
			$this->assertDatabaseHas('test_table', [
				'id'   => 1,
				'name' => 'name a updated',
				'x'    => 12,
			]);
			$this->assertDatabaseHas('test_table', [
				'id'   => 2,
				'name' => 'name b updated',
				'x'    => 22,
			]);

			// not updated
			$this->assertDatabaseHas('test_table', [
				'id'   => 3,
				'name' => 'name c',
				'x'    => 31
			]);

			// not inserted
			$this->assertDatabaseMissing('test_table', [
				'id'   => 4,
			]);

		}


		public function testUpdateWithJoinedData_customParams() {

			DB::table('test_table')->insert([
				'id' => 1,
				'name' => 'name a',
				'x' => 11
			]);
			DB::table('test_table')->insert([
				'id' => 2,
				'name' => 'name b',
				'x' => 21
			]);
			DB::table('test_table')->insert([
				'id' => 3,
				'name' => 'name c',
				'x' => 31
			]);

			DB::table('test_table')
				->updateWithJoinedData(
						[
						[
							'id'   => 1,
							'name' => 'name a updated',
							'x'    => 12,
						],
						[
							'id'   => 2,
							'name' => 'name b updated',
							'x'    => 22,
						],
						[
							'id'   => 4,
							'name' => 'name b updated',
							'x'    => 42,
						],
					],
					['id'],
					[
						'name',
						'x' => new Expression('myData.x + 100')
					],
					'myData'
				);

			// updated
			$this->assertDatabaseHas('test_table', [
				'id'   => 1,
				'name' => 'name a updated',
				'x'    => 112,
			]);
			$this->assertDatabaseHas('test_table', [
				'id'   => 2,
				'name' => 'name b updated',
				'x'    => 122,
			]);

			// not updated
			$this->assertDatabaseHas('test_table', [
				'id'   => 3,
				'name' => 'name c',
				'x'    => 31
			]);

			// not inserted
			$this->assertDatabaseMissing('test_table', [
				'id'   => 4,
			]);

		}

	}