<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 28.11.18
	 * Time: 14:14
	 */
	class CreateBelongsBelongsTable extends Migration
	{
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up() {

			Schema::create('belongs_belongs_table', function (Blueprint $table) {
				$table->bigIncrements('id');
				$table->unsignedBigInteger('belongs_table_id');
				$table->string('b_name', 255);
				$table->string('b_x', 255);
				$table->foreign('belongs_table_id')->references('id')->on('belongs_table');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down() {
			Schema::dropIfExists('belongs_belongs_table');
		}
	}