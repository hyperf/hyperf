<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection\Cache;

use Hyperf\Framework\ApplicationContext;

trait Cacheable
{
    /**
     * Fetch a model from cache.
     * @return null|self
     */
    public static function findFromCache($id)
    {
        $container = ApplicationContext::getContainer();
        $manager = $container->get(Manager::class);

        return $manager->findFromCache($id, static::class);
    }

    /**
     * Fetch models from cache.
     * @return \Hyperf\Database\Model\Collection
     */
    public static function findManyFromCache($ids)
    {
        $container = ApplicationContext::getContainer();
        $manager = $container->get(Manager::class);

        return $manager->findManyFromCache($ids, static::class);
    }

    /**
     * Delete model from cache.
     * @return bool
     */
    public function deleteCache()
    {
        $manager = $this->getContainer()->get(Manager::class);

        return $manager->destroy([$this->getKey()], get_called_class());
    }

    /**
     * Increment a column's value by a given amount.
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
}
