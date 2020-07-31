<?php


	namespace MehrIt\LaraDbExt\Eloquent;


	use MehrIt\LaraDbExt\Eloquent\Concerns\ChunkedModelGenerate;
	use MehrIt\LaraDbExt\Eloquent\Concerns\InsertModels;
	use MehrIt\LaraDbExt\Eloquent\Concerns\InsertModelsOnDuplicateKey;
	use MehrIt\LaraDbExt\Eloquent\Concerns\ModelData;
	use MehrIt\LaraDbExt\Eloquent\Concerns\UpdateWithJoinedModels;
	use MehrIt\LaraDbExt\Eloquent\Concerns\WithJoined;

	class Builder extends \Illuminate\Database\Eloquent\Builder
	{
		use ChunkedModelGenerate;
		use InsertModels;
		use InsertModelsOnDuplicateKey;
		use ModelData;
		use UpdateWithJoinedModels;
		use WithJoined;

		/**
		 * Create a new Eloquent query builder instance.
		 *
		 * @param \Illuminate\Database\Query\Builder $query
		 * @return void
		 */
		public function __construct(\Illuminate\Database\Query\Builder $query) {
			$this->query = $query;
		}

		/**
		 * @inheritDoc
		 */
		public function getModels($columns = ['*']) {

			return $this->hasModelsJoined() ? $this->getModelsWithJoined($columns) : parent::getModels($columns);
		}

		/**
		 * @inheritDoc
		 */
		public function cursor() {
			yield from ($this->hasModelsJoined() ? $this->cursorWithJoined([]) : parent::cursor());
		}

		/**
		 * @inheritDoc
		 */
		protected function prepareForYield($data) {
			return $this->model->newFromBuilder($data);
		}


	}