<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\ModelCache\EagerLoad;

use Hyperf\Database\Connection;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Query\Builder as QueryBuilder;
use Psr\Container\ContainerInterface;

class EagerLoader
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(Collection $collection, array $relations)
    {
        if ($collection->isNotEmpty()) {
            /** @var Model $first */
            $first = $collection->first();
            $query = $first->registerGlobalScopes((new EagerLoaderBuilder($this->newBaseQueryBuilder($first)))->setModel($first))->with($relations);
            $collection->fill($query->eagerLoadRelations($collection->all()));
        }
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
