<?php


	namespace MehrItLaraDbExtTest\Cases\Unit\Model;


	use Illuminate\Database\Eloquent\Model;
	use MehrIt\LaraDbExt\Model\Identifiers;
	use MehrItLaraDbExtTest\Cases\TestCase;

	class IdentifiersTest extends TestCase
	{
		public function testTable() {
			$this->assertEquals('test_table', IdentifiersTestModel::table());
		}

		public function testTable_notPrefixed() {
			$this->assertEquals('test_table', IdentifiersTestModel::table(false));
		}

		public function testTableRaw() {
			$this->assertEquals('"test_table"', IdentifiersTestModel::tableRaw());
		}

		public function testField() {
			$this->assertEquals('test_table.id', IdentifiersTestModel::field('id'));
		}

		public function testField_notPrefixed() {
			$this->assertEquals('test_table.id', IdentifiersTestModel::field('id', false));
		}

		public function testFieldRaw() {
			$this->assertEquals('"test_table"."id"', IdentifiersTestModel::fieldRaw('id'));
		}

		public function testQuoteIdentifier() {
			$this->assertEquals('"id"', IdentifiersTestModel::quoteIdentifier('id'));
		}

		public function testQuoteIdentifier_segments() {
			$this->assertEquals('"a_table"."id"', IdentifiersTestModel::quoteIdentifier('a_table.id'));
		}

		public function testTable_connectionWithTablePrefix() {
			$this->assertEquals('myPfx_test_table', IdentifiersTestModelPrefixed::table());
		}

		public function testTable_notPrefixed_connectionWithTablePrefix() {
			$this->assertEquals('test_table', IdentifiersTestModelPrefixed::table(false));
		}

		public function testTableRaw_connectionWithTablePrefix() {
			$this->assertEquals('"myPfx_test_table"', IdentifiersTestModelPrefixed::tableRaw());
		}

		public function testField_connectionWithTablePrefix() {
			$this->assertEquals('myPfx_test_table.id', IdentifiersTestModelPrefixed::field('id'));
		}

		public function testField_notPrefixed_connectionWithTablePrefix() {
			$this->assertEquals('test_table.id', IdentifiersTestModelPrefixed::field('id', false));
		}

		public function testFieldRaw_connectionWithTablePrefix() {
			$this->assertEquals('"myPfx_test_table"."id"', IdentifiersTestModelPrefixed::fieldRaw('id'));
		}

		public function testQuoteIdentifier_connectionWithTablePrefix() {
			$this->assertEquals('"id"', IdentifiersTestModelPrefixed::quoteIdentifier('id'));
		}

		public function testQuoteIdentifier_segments_connectionWithTablePrefix() {
			$this->assertEquals('"a_table"."id"', IdentifiersTestModelPrefixed::quoteIdentifier('a_table.id'));
		}
	}

	class IdentifiersTestModel extends Model {
		use Identifiers;

		protected $connection = 'dummyMocked';

		protected $table = 'test_table';
	}

	class IdentifiersTestModelPrefixed extends Model {
		use Identifiers;

		protected $connection = 'dummyMockedPrefixed';

		protected $table = 'test_table';
	}