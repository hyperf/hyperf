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

use Closure;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\ModelCache\CacheableInterface;
use Hyperf\Utils\Arr;

class EagerLoaderBuilder extends Builder
{
    protected function eagerLoadRelation(array $models, $name, Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $constraints($relation);

        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        return $relation->match(
            $relation->initRelation($models, $name),
            $this->getEagerModels($relation),
            $name
        );
    }

    protected function getEagerModels(Relation $relation)
    {
        $wheres = $relation->getQuery()->getQuery()->wheres;
        $model = $relation->getModel();
        $column = sprintf('%s.%s', $model->getTable(), $model->getKeyName());
        if (count($wheres) === 1
            && $model instanceof CacheableInterface
            && Arr::get($wheres[0], 'type') === 'InRaw'
            && Arr::get($wheres[0], 'column') === $column) {
            return $model::findManyFromCache($wheres[0]['values'] ?? []);
        }

        return $relation->getEager();
    }
}
