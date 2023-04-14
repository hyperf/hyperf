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
use Hyperf\Collection\Arr;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\ModelCache\CacheableInterface;

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

        if ($model instanceof CacheableInterface && $this->couldUseEagerLoad($wheres, $column)) {
            $models = $model::findManyFromCache($wheres[0]['values'] ?? []);
            // If we actually found models we will also eager load any relationships that
            // have been specified as needing to be eager loaded, which will solve the
            // n+1 query issue for the developers to avoid running a lot of queries.
            if ($models->count() > 0 && $with = $relation->getEagerLoads()) {
                $first = $models->first();
                $self = (new static($this->query->newQuery()));
                $builder = $first->registerGlobalScopes($self->setModel($first))->setEagerLoads($with);
                $models = new Collection($builder->eagerLoadRelations($models->all()));
            }

            return $models;
        }

        return $relation->getEager();
    }

    protected function couldUseEagerLoad(array $wheres, string $column): bool
    {
        return count($wheres) === 1
            && in_array(Arr::get($wheres[0], 'type'), ['In', 'InRaw'], true)
            && Arr::get($wheres[0], 'column') === $column
            && $this->isValidValues($wheres[0]['values'] ?? []);
    }

    protected function isValidValues(array $values): bool
    {
        foreach ($values as $value) {
            if (! is_int($value) && ! is_string($value)) {
                return false;
            }
        }
        return true;
    }
}
