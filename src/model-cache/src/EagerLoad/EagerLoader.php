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
    public function load(Collection $collection, array $relations)
    {
        if ($collection->isNotEmpty()) {
            /** @var Model $first */
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
     *
     * @return \Hyperf\Database\Query\Builder
     */
    protected function newBaseQueryBuilder(Model $model)
    {
        /** @var Connection $connection */
        $connection = $model->getConnection();

        return new QueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }
}
