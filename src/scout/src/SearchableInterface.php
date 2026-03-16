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

namespace Hyperf\Scout;

/**
 * Only use to fix phpstan.
 */
interface SearchableInterface
{
    /**
     * Get the requested models from an array of object IDs.
     */
    public function getScoutModelsByIds(Builder $builder, array $ids);

    /**
     * Get the Scout engine for the model.
     *
     * @return mixed
     */
    public function searchableUsing();

    /**
     * Remove the given model instance from the search index.
     */
    public function unsearchable(): void;

    /**
     * Make the given model instance searchable.
     */
    public function searchable(): void;

    /**
     * Get the index name for the model.
     */
    public function searchableAs(): string;

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool;
}
