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

namespace Hyperf\ModelCache;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Query\Builder as QueryBuilder;
use Hyperf\ModelCache\Builder as ModelCacheBuilder;

trait Cacheable
{
    protected bool $useCacheBuilder = false;

    /**
     * Fetch a model from cache.
     * @param mixed $id
     * @return null|self
     */
    public static function findFromCache($id): ?Model
    {
        $container = ApplicationContext::getContainer();
        $manager = $container->get(Manager::class);

        return $manager->findFromCache($id, static::class);
    }

    /**
     * Fetch models from cache.
     * @return Collection<int, self>
     */
    public static function findManyFromCache(array $ids): Collection
    {
        $container = ApplicationContext::getContainer();
        $manager = $container->get(Manager::class);

        $ids = array_unique($ids);
        return $manager->findManyFromCache($ids, static::class);
    }

    /**
     * Delete model from cache.
     */
    public function deleteCache(): bool
    {
        $manager = $this->getContainer()->get(Manager::class);

        return $manager->destroy([$this->getKey()], get_called_class());
    }

    /**
     * Get the expired time for cache.
     */
    public function getCacheTTL(): ?int
    {
        return null;
    }

    /**
     * Increment a column's value by a given amount.
     * @param string $column
     * @param float|int $amount
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        $res = parent::increment($column, $amount, $extra);
        if ($res > 0) {
            if ($this->getConnection()->transactionLevel() && $this instanceof CacheableInterface) {
                InvalidCacheManager::instance()->push($this);
            } elseif (empty($extra)) {
                // Only increment a column's value.
                /** @var Manager $manager */
                $manager = $this->getContainer()->get(Manager::class);
                $manager->increment($this->getKey(), $column, $amount, get_called_class());
            } else {
                // Update other columns, when increment a column's value.
                $this->deleteCache();
            }
        }
        return $res;
    }

    /**
     * Decrement a column's value by a given amount.
     * @param string $column
     * @param float|int $amount
     * @return int
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        $res = parent::decrement($column, $amount, $extra);
        if ($res > 0) {
            if ($this->getConnection()->transactionLevel() && $this instanceof CacheableInterface) {
                InvalidCacheManager::instance()->push($this);
            } elseif (empty($extra)) {
                // Only decrement a column's value.
                /** @var Manager $manager */
                $manager = $this->getContainer()->get(Manager::class);
                $manager->increment($this->getKey(), $column, -$amount, get_called_class());
            } else {
                // Update other columns, when decrement a column's value.
                $this->deleteCache();
            }
        }
        return $res;
    }

    /**
     * Create a new Model query builder for the model.
     * @param QueryBuilder $query
     */
    public function newModelBuilder($query): Builder
    {
        if ($this->useCacheBuilder) {
            return new ModelCacheBuilder($query);
        }

        return parent::newModelBuilder($query);
    }

    public function newQuery(bool $cache = false): Builder
    {
        $this->useCacheBuilder = $cache;
        return parent::newQuery();
    }

    /**
     * @param bool $cache Whether to delete the model cache when batch update
     * @return Builder|static
     */
    public static function query(bool $cache = false): Builder
    {
        return (new static())->newQuery($cache);
    }
}
