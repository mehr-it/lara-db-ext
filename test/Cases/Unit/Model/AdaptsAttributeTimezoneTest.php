<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Model;


	use Carbon\Carbon;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrIt\LaraDbExt\Connection\AdaptsTimezone;
	use MehrIt\LaraDbExt\Model\AdaptsAttributeTimezone;
	use MehrItLaraDbExtTest\Cases\TestCase;

	class AdaptsAttributeTimezoneTest extends TestCase
	{
		use DatabaseTransactions;

		public function testAdapts() {


			if (date_default_timezone_get() != 'UTC')
				$this->markTestSkipped('This test expects the current timezone to be UTC, but it is ' . date_default_timezone_get());

			// we need timezone adapting connection here
			$this->assertContains(AdaptsTimezone::class, class_uses_recursive((new AdaptsAttributeTimezoneTestModelAdapting())->getConnection()));
			$this->assertTrue((new AdaptsAttributeTimezoneTestModelAdapting())->getConnection()->getConfig('adapt_timezone'));


			$now = (new Carbon())->setTimezone('Europe/Berlin');

			$model     = new AdaptsAttributeTimezoneTestModelAdapting();
			$model->dt = $now;

			$this->assertSame($now->copy()->setTimezone('UTC')->format($model->getDateFormat()), $model->getAttributes()['dt']);

		}

		public function testAdapts_save() {


			if (date_default_timezone_get() != 'UTC')
				$this->markTestSkipped('This test expects the current timezone to be UTC, but it is ' . date_default_timezone_get());

			// we need timezone adapting connection here
			$this->assertContains(AdaptsTimezone::class, class_uses_recursive((new AdaptsAttributeTimezoneTestModelAdapting())->getConnection()));
			$this->assertTrue((new AdaptsAttributeTimezoneTestModelAdapting())->getConnection()->getConfig('adapt_timezone'));


			$now = (new Carbon())->setTimezone('Europe/Berlin');


			$written       = new AdaptsAttributeTimezoneTestModelAdapting();
			$written->name = 'a';
			$written->x    = 'b';
			$written->dt   = $now;
			$written->save();


			$this->assertSame('Europe/Berlin', $now->getTimezone()->getName());

			$read = (new AdaptsAttributeTimezoneTestModelAdapting())->find($written->id);


			$this->assertSame($now->getTimestamp(), $read->dt->getTimestamp());

		}

		public function testNotAdapts() {


			if (date_default_timezone_get() != 'UTC')
				$this->markTestSkipped('This test expects the current timezone to be UTC, but it is ' . date_default_timezone_get());

			// we need a connection not adapting timezone here
			$this->assertFalse((bool)(new AdaptsAttributeTimezoneTestModelNotAdapting())->getConnection()->getConfig('adapt_timezone'));

			$now = (new Carbon())->setTimezone('Europe/Berlin');

			$model     = new AdaptsAttributeTimezoneTestModelNotAdapting();
			$model->dt = $now;

			$this->assertSame($now->format($model->getDateFormat()), $model->getAttributes()['dt']);

		}

		public function testNotAdapts_save() {


			if (date_default_timezone_get() != 'UTC')
				$this->markTestSkipped('This test expects the current timezone to be UTC, but it is ' . date_default_timezone_get());

			// we need a connection not adapting timezone here
			$this->assertFalse((bool)(new AdaptsAttributeTimezoneTestModelNotAdapting())->getConnection()->getConfig('adapt_timezone'));


			$now = (new Carbon())->setTimezone('Europe/Berlin');


			$written       = new AdaptsAttributeTimezoneTestModelNotAdapting();
			$written->name = 'a';
			$written->x    = 'b';
			$written->dt   = $now;
			$written->save();


			$this->assertSame('Europe/Berlin', $now->getTimezone()->getName());

			$read = (new AdaptsAttributeTimezoneTestModelNotAdapting())->find($written->id);


			$this->assertSame($now->getTimestamp() + $now->getTimezone()->getOffset($now), $read->dt->getTimestamp());

		}


	}

	class AdaptsAttributeTimezoneTestModelAdapting extends Model {

		use AdaptsAttributeTimezone;

		protected $table = 'test_table';

		protected $connection = 'adapt-timezone-connection';

		protected $dates = [
			'dt',
		];
	}

	class AdaptsAttributeTimezoneTestModelNotAdapting extends Model {

		use AdaptsAttributeTimezone;

		protected $table = 'test_table';

		protected $dates = [
			'dt',
		];
	}