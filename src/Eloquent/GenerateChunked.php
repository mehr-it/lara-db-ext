<?php


	namespace MehrIt\LaraDbExt\Eloquent;


	use MehrIt\LaraDbExt\Query\GenerateChunked as GenerateChunkedQuery;
	use RuntimeException;

	class GenerateChunked extends GenerateChunkedQuery
	{
		/**
		 * @inheritDoc
		 */
		protected function enforceOrderBy() {

			$builder = $this->builder;
			$queryBuilder = $builder->getQuery();

			// enforce order by
			if (empty($queryBuilder->orders) && empty($queryBuilder->unionOrders)) {

				$model = $builder->getModel();

				if (!$model->getKeyName())
					throw new RuntimeException('You must specify an orderBy clause when the model does not have a primary key');

				$builder->orderBy($builder->getModel()->getQualifiedKeyName());

			}
		}

	}