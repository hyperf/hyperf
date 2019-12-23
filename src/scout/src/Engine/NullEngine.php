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

namespace Hyperf\Scout\Engine;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\Collection as BaseCollection;

class NullEngine extends Engine
{
    /**
     * Update the given model in the index.
     */
    public function update(Collection $models): void
    {
    }

    /**
     * Remove the given model from the index.
     */
    public function delete(Collection $models): void
    {
    }

    /**
     * Perform the given search on the engine.
     */
    public function search(Builder $builder)
    {
        return [];
    }

    /**
     * Perform the given search on the engine.
     */
    public function paginate(Builder $builder, int $perPage, int $page)
    {
        return [];
    }

    /**
     * Pluck and return the primary keys of the given results.
     * @param mixed $results
     */
    public function mapIds($results): Collection
    {
        return BaseCollection::make();
    }

    /**
     * Map the given results to instances of the given model.
     * @param mixed $results
     */
    public function map(Builder $builder, $results, Model $model): Collection
    {
        return Collection::make();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     * @param mixed $results
     */
    public function getTotalCount($results): int
    {
        return count($results);
    }

    /**
     * Flush all of the model's records from the engine.
     */
    public function flush(Model $model): void
    {
    }
}
