<?php


	namespace MehrIt\LaraDbExt\Provider;


	use Illuminate\Support\ServiceProvider;

	class LaraDbExtServiceProvider extends ServiceProvider
	{
		use RegistersBuilderMacros;

		public function boot() {

			$this->registerQueryBuilderMacros();
			$this->registerEloquentBuilderMacros();
		}

	}