<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Model;


	use Carbon\Carbon;
	use DB;
	use Illuminate\Database\MySqlConnection;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrItLaraDbExtTest\Cases\TestCase;
	use MehrItLaraDbExtTest\Model\TestModel;
	use PHPUnit\Framework\SkippedTestError;

	class SerializeDateFormatTest extends TestCase
	{
		use DatabaseTransactions;

		/**
		 * @inheritdoc
		 */
		public function setUp(): void {
			parent::setUp();

			// we need a MySQL connection
			if (!(DB::connection() instanceof MySqlConnection))
				throw new SkippedTestError('This test requires a MySQL connection.');
		}

		public function testWithDateFormat_formatOnly() {


			/** @var TestModel $m1 */
			$m1 = factory(TestModel::class)->create();

			$testFormat = 'd.m.y H:i';
			$origFormat = $m1->getDateFormat();

			if ($testFormat === $origFormat)
				$this->markTestSkipped('Test date format equals model\'s native format');

			// check that date's are correctly formatted
			$ret = $m1->withDateFormat($testFormat, null, function (TestModel $model) use ($testFormat) {
				$this->assertSame($model->created_at->format($testFormat), $model->toArray()['created_at']);

				return 17;
			});
			$this->assertSame(17, $ret);


			// check that original format is reverted
			$this->assertSame($m1->created_at->format($origFormat), $m1->toArray()['created_at']);
		}

		public function testWithDateFormat_formatAndTimezone() {


			/** @var TestModel $m1 */
			$m1 = factory(TestModel::class)->create();

			$testTimezone = 'Europe/Berlin';

			$testFormat = 'd.m.y H:i';
			$origFormat = $m1->getDateFormat();

			if ($testFormat === $origFormat)
				$this->markTestSkipped('Test date format equals model\'s native format');

			$now = new Carbon();

			// check that date's are correctly formatted
			$ret = $m1->withDateFormat($testFormat, $testTimezone, function (TestModel $model) use ($testFormat, $testTimezone, $now) {

				if ($model->created_at->getTimezone()->getOffset($now) == (new \DateTimeZone($testTimezone))->getOffset($now))
					$this->markTestSkipped('Test timezone offset matches model\'s native timezone offset');


				$this->assertSame($model->created_at->copy()->setTimezone($testTimezone)->format($testFormat), $model->toArray()['created_at']);

				return 17;
			});
			$this->assertSame(17, $ret);


			// check that original format is reverted
			$this->assertSame($m1->created_at->format($origFormat), $m1->toArray()['created_at']);
		}

		public function testWithDateFormat_timezoneOnly() {


			/** @var TestModel $m1 */
			$m1 = factory(TestModel::class)->create();

			$testTimezone = 'Europe/Berlin';

			$origFormat = $m1->getDateFormat();


			$now = new Carbon();

			// check that date's are correctly formatted
			$ret = $m1->withDateFormat(null, $testTimezone, function (TestModel $model) use ($testTimezone, $now) {

				if ($model->created_at->getTimezone()->getOffset($now) == (new \DateTimeZone($testTimezone))->getOffset($now))
					$this->markTestSkipped('Test timezone offset matches model\'s native timezone offset');


				$this->assertSame($model->created_at->copy()->setTimezone($testTimezone)->format($model->getDateFormat()), $model->toArray()['created_at']);

				return 17;
			});
			$this->assertSame(17, $ret);


			// check that original format is reverted
			$this->assertSame($m1->created_at->format($origFormat), $m1->toArray()['created_at']);
		}

		public function testWithDateFormat_exceptionInClosure() {


			/** @var TestModel $m1 */
			$m1 = factory(TestModel::class)->create();

			$testFormat = 'd.m.y H:i';
			$origFormat = $m1->getDateFormat();

			if ($testFormat === $origFormat)
				$this->markTestSkipped('Test date format equals model\'s native format');

			// check that date's are correctly formatted and throw exception
			try {
				$m1->withDateFormat($testFormat, null, function (TestModel $model) use ($testFormat) {
					$this->assertSame($model->created_at->format($testFormat), $model->toArray()['created_at']);

					throw new \RuntimeException();
				});
				$this->assertFalse(true);
			}
			catch (\RuntimeException $ex) {
				$this->assertFalse(false);
			}


			// check that original format is reverted
			$this->assertSame($m1->created_at->format($origFormat), $m1->toArray()['created_at']);
		}
	}