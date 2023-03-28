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
namespace Hyperf\Scout\Engine;

use Hyperf\Collection\Collection as BaseCollection;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Scout\Builder;

abstract class Engine
{
    /**
     * Update the given model in the index.
     */
    abstract public function update(Collection $models): void;

    /**
     * Remove the given model from the index.
     */
    abstract public function delete(Collection $models): void;

    /**
     * Perform the given search on the engine.
     */
    abstract public function search(Builder $builder);

    /**
     * Perform the given search on the engine.
     */
    abstract public function paginate(Builder $builder, int $perPage, int $page);

    /**
     * Pluck and return the primary keys of the given results.
     * @param mixed $results
     */
    abstract public function mapIds($results): BaseCollection;

    /**
     * Map the given results to instances of the given model.
     * @param mixed $results
     */
    abstract public function map(Builder $builder, $results, Model $model): Collection;

    /**
     * Get the total count from a raw result returned by the engine.
     * @param mixed $results
     */
    abstract public function getTotalCount($results): int;

    /**
     * Flush all of the model's records from the engine.
     */
    abstract public function flush(Model $model): void;

    /**
     * Get the results of the query as a Collection of primary keys.
     */
    public function keys(Builder $builder): BaseCollection
    {
        return $this->mapIds($this->search($builder));
    }

    /**
     * Get the results of the given query mapped onto models.
     */
    public function get(Builder $builder): Collection
    {
        return $this->map(
            $builder,
            $this->search($builder),
            $builder->model
        );
    }
}
