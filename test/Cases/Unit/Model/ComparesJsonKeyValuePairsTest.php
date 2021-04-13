<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Model;


	use Illuminate\Database\Eloquent\Model;
	use MehrIt\LaraDbExt\Model\ComparesJsonKeyValuePairs;
	use MehrItLaraDbExtTest\Cases\TestCase;

	class ComparesJsonKeyValuePairsTest extends TestCase
	{

		public function testDirtyAttributes() {
			$model = new ComparesArrayKeyValuePairsModelStub(['foo' => '1', 'bar' => 2, 'baz' => 3]);
			$model->syncOriginal();
			$model->foo = 1;
			$model->bar = 20;
			$model->baz = 30;

			$this->assertTrue($model->isDirty());
			$this->assertFalse($model->isDirty('foo'));
			$this->assertTrue($model->isDirty('bar'));
			$this->assertTrue($model->isDirty('foo', 'bar'));
			$this->assertTrue($model->isDirty(['foo', 'bar']));
		}

		public function testDirtyOnCastOrDateAttributes() {
			$model = new ComparesArrayKeyValuePairsModelCastingStub;
			$model->setDateFormat('Y-m-d H:i:s');
			$model->boolAttribute     = 1;
			$model->foo               = 1;
			$model->bar               = '2017-03-18';
			$model->dateAttribute     = '2017-03-18';
			$model->datetimeAttribute = '2017-03-23 22:17:00';
			$model->syncOriginal();

			$model->boolAttribute     = true;
			$model->foo               = true;
			$model->bar               = '2017-03-18 00:00:00';
			$model->dateAttribute     = '2017-03-18 00:00:00';
			$model->datetimeAttribute = null;

			$this->assertTrue($model->isDirty());
			$this->assertTrue($model->isDirty('foo'));
			$this->assertTrue($model->isDirty('bar'));
			$this->assertFalse($model->isDirty('boolAttribute'));
			$this->assertFalse($model->isDirty('dateAttribute'));
			$this->assertTrue($model->isDirty('datetimeAttribute'));
		}

		public function testDirtyOnJsonAttributes_arrayWithDifferentOrder() {
			$array = ['a' => 'foo', 'b' => ['c' => 'bar', 'd' => 'baz']];
			$model = new ComparesArrayKeyValuePairsModelCastingStub;
			$model->setRawAttributes(['objectAttribute' => '{"a": "foo"}']);
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;
			$model->syncOriginal();

			$array                      = ['b' => ['d' => 'baz', 'c' => 'bar'], 'a' => 'foo'];
			$model->objectAttribute     = ['a' => 'foo'];
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;

			$this->assertFalse($model->isDirty());

			$model->objectAttribute = ['a' => 'bar'];

			$this->assertTrue($model->isDirty());
		}
		
		public function testDirtyOnJsonAttributes_arrayWithSameOrder() {
			$array = ['a' => 'foo', 'b' => ['c' => 'bar', 'd' => 'baz']];
			$model = new ComparesArrayKeyValuePairsModelCastingStub;
			$model->setRawAttributes(['objectAttribute' => '{"a": "foo"}']);
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;
			$model->syncOriginal();

			$array                      = ['a' => 'foo', 'b' => ['c' => 'bar', 'd' => 'baz']];
			$model->objectAttribute     = ['a' => 'foo'];
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;

			$this->assertFalse($model->isDirty());

			$model->objectAttribute = ['a' => 'bar'];

			$this->assertTrue($model->isDirty());
		}

		public function testDirtyOnJsonAttributes_arrayWithDeepValueChanged() {
			$array = ['a' => 'foo', 'b' => ['c' => 'bar', 'd' => 'baz']];
			$model = new ComparesArrayKeyValuePairsModelCastingStub;
			$model->setRawAttributes(['objectAttribute' => '{"a": "foo"}']);
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;
			$model->syncOriginal();

			$array                      = ['b' => ['d' => 'baz', 'c' => 'CHANGED'], 'a' => 'foo'];
			$model->objectAttribute     = ['a' => 'foo'];
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;

			$this->assertTrue($model->isDirty());
			
		}
		
		public function testDirtyOnJsonAttributes_arrayWithDeepValueMissing() {
			$array = ['a' => 'foo', 'b' => ['c' => 'bar', 'd' => 'baz']];
			$model = new ComparesArrayKeyValuePairsModelCastingStub;
			$model->setRawAttributes(['objectAttribute' => '{"a": "foo"}']);
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;
			$model->syncOriginal();

			$array                      = ['b' => ['d' => 'baz'], 'a' => 'foo'];
			$model->objectAttribute     = ['a' => 'foo'];
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;

			$this->assertTrue($model->isDirty());
			
		}
		
		public function testDirtyOnJsonAttributes_arrayWithDeepValueAdded() {
			$array = ['a' => 'foo', 'b' => ['c' => 'bar', 'd' => 'baz']];
			$model = new ComparesArrayKeyValuePairsModelCastingStub;
			$model->setRawAttributes(['objectAttribute' => '{"a": "foo"}']);
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;
			$model->syncOriginal();

			$array                      = ['b' => ['d' => 'baz', 'c' => 'bar', 'e' => null], 'a' => 'foo'];
			$model->objectAttribute     = ['a' => 'foo'];
			$model->arrayAttribute      = $array;
			$model->jsonAttribute       = $array;
			$model->collectionAttribute = $array;

			$this->assertTrue($model->isDirty());
			
		}

		public function testCleanAttributes() {
			$model = new ComparesArrayKeyValuePairsModelStub(['foo' => '1', 'bar' => 2, 'baz' => 3]);
			$model->syncOriginal();
			$model->foo = 1;
			$model->bar = 20;
			$model->baz = 30;

			$this->assertFalse($model->isClean());
			$this->assertTrue($model->isClean('foo'));
			$this->assertFalse($model->isClean('bar'));
			$this->assertFalse($model->isClean('foo', 'bar'));
			$this->assertFalse($model->isClean(['foo', 'bar']));
		}
		
	}
	
	class ComparesArrayKeyValuePairsModelCastingStub extends Model {
		
		use ComparesJsonKeyValuePairs;

		protected $casts = [
			'intAttribute'        => 'int',
			'floatAttribute'      => 'float',
			'stringAttribute'     => 'string',
			'boolAttribute'       => 'bool',
			'booleanAttribute'    => 'boolean',
			'objectAttribute'     => 'object',
			'arrayAttribute'      => 'array',
			'jsonAttribute'       => 'json',
			'collectionAttribute' => 'collection',
			'dateAttribute'       => 'date',
			'datetimeAttribute'   => 'datetime',
			'timestampAttribute'  => 'timestamp',
		];

		public function jsonAttributeValue() {
			return $this->attributes['jsonAttribute'];
		}
	}
	class ComparesArrayKeyValuePairsModelStub extends Model {
		
		use ComparesJsonKeyValuePairs;

		protected $guarded = [];
		
	}