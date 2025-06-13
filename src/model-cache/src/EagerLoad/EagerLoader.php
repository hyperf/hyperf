<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ModelCache\EagerLoad;

use Hyperf\Database\Connection;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Query\Builder as QueryBuilder;

class EagerLoader
{
    /**
     * @param Collection<int, Model> $collection
     */
    public function load(Collection $collection, array $relations): void
    {
        if ($collection->isNotEmpty()) {
            $first = $collection->first();
            $query = $first->registerGlobalScopes($this->newBuilder($first))->with($relations);
            $collection->fill($query->eagerLoadRelations($collection->all()));
        }
    }

    protected function newBuilder(Model $model): Builder
    {
        $builder = new EagerLoaderBuilder($this->newBaseQueryBuilder($model));

        return $builder->setModel($model);
    }

    /**
     * Get a new query builder instance for the connection.
     */
    protected function newBaseQueryBuilder(Model $model): QueryBuilder
    {
        /** @var Connection $connection */
        $connection = $model->getConnection();

        return new QueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }
}
