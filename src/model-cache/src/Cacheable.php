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

namespace Hyperf\ModelCache;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;

trait Cacheable
{
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
     * @param mixed $ids
     */
    public static function findManyFromCache($ids): Collection
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
     * Increment a column's value by a given amount.
     * @param mixed $column
     * @param mixed $amount
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        $res = parent::increment($column, $amount, $extra);
        if ($res > 0) {
            if (empty($extra)) {
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
     * @param mixed $column
     * @param mixed $amount
     * @return int
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        $res = parent::decrement($column, $amount, $extra);
        if ($res > 0) {
            if (empty($extra)) {
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
     * @param bool $cache Whether to delete the model cache when batch update
     * @return Builder
     */
    public static function query(bool $cache = false)
    {
        $query = parent::query();

        if ($cache) {
            $modelName = static::class;
            $query->onDelete(function (Builder $builder) use ($modelName) {
                $queryBuilder = clone $builder;
                /** @var \Hyperf\DbConnection\Model\Model $instance */
                $instance = new $modelName();
                $primaryKey = $instance->getKeyName();
                $ids = [];
                $models = $queryBuilder->get([$primaryKey]);
                foreach ($models as $model) {
                    $ids[] = $model->{$primaryKey};
                }
                if (empty($ids)) {
                    return 0;
                }

                $result = $builder->toBase()->delete();

                $manger = ApplicationContext::getContainer()->get(Manager::class);

                $manger->destroy($ids, $modelName);

                return $result;
            });
        }

        return $query;
    }
}
