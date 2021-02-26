<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Model;


	use Carbon\Carbon;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\TestModel;
	use MehrItLaraDbExtTest\Model\TestModelWithMutations;

	class ModificationsTest extends TestCase
	{
		use DatabaseTransactions;
		
		public function testGetModifications() {
			
			$now = Carbon::now();
			
			
			$model       = new TestModel();
			$model->name = 'a';
			$model->x    = 'b';
			$model->dt   = $now;
			$model->save();
			
			$model->name = 'c';
			$model->dt = $now->copy()->addSeconds(10);
			
			$this->assertEquals([
				'name' => ['a', 'c'],
				'dt'   => [$now, $now->copy()->addSeconds(10)]
			], $model->getModifications());
		}
		
		public function testGetModifications_withMutators() {
			
			$now = Carbon::now();
			
			
			$model       = new TestModelWithMutations();
			$model->name = 'a';
			$model->x    = 'b';
			$model->dt   = $now;
			$model->save();
			
			$model->name = 'c';
			
			$this->assertEquals([
				'name' => ['a', 'c'],
			], $model->getModifications());
		}
		
		public function testGetModifications_withMutators_unchanged() {
			
			$now = Carbon::now();
			
			
			$model       = new TestModelWithMutations();
			$model->name = 'a';
			$model->x    = 'b';
			$model->dt   = $now;
			$model->save();
			
			$model->name = 'A';
			
			$this->assertEquals([], $model->getModifications());
		}
	}