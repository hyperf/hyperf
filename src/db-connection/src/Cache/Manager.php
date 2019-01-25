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

use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Cache\Handler\HandlerInterface;
use Hyperf\DbConnection\Cache\Handler\RedisHandler;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;

class Manager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var HandlerInterface[]
     */
    protected $handlers = [];

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $config = $container->get(ConfigInterface::class);
        if (! $config->has('databases')) {
            throw new \InvalidArgumentException('config databases is not exist!');
        }

        foreach ($config->get('databases') as $key => $item) {
            $handler = $item['handler'] ?? RedisHandler::class;
            $config = new Config($item['cache'] ?? [], $key);
            $this->handlers[$key] = new $handler($this->container, $config);
        }
    }

    public function findFromCache($id, string $class)
    {
        /** @var Model $instance */
        $instance = new $class();

        $name = $instance->getConnectionName();
        $primaryKey = $instance->getKeyName();

        if ($handler = $this->handlers[$name] ?? null) {
            $key = $this->getCacheKey($id, $instance, $handler->getConfig());
            $data = $handler->get($key);
            if ($data) {
                return $instance->newInstance($data, true);
            }

            // Fetch it from database, because it not exist in cache handler.
            if (is_null($data)) {
                $model = $instance->newQuery()->where($primaryKey, '=', $id)->first();
                if ($model) {
                    $handler->set($key, $model->toArray());
                } else {
                    $handler->set($key, []);
                }
                return $model;
            }

            // It not exist in cache handler and database.
            return null;
        }

        $this->logger->alert('Cache handler not exist, fetch data from database.');
        return $instance->newQuery()->where($primaryKey, '=', $id)->first();
    }

    public function findManyFromCache(array $ids, string $class)
    {
        /** @var Model $instance */
        $instance = new $class();

        $name = $instance->getConnectionName();
        $primaryKey = $instance->getKeyName();

        if ($handler = $this->handlers[$name] ?? null) {
            $keys = [];
            foreach ($ids as $id) {
                $keys[] = $this->getCacheKey($id, $instance, $handler->getConfig());
            }
            $data = $handler->getMultiple($keys);
            $items = [];
            $fetchIds = [];
            foreach ($data ?? [] as $item) {
                if (isset($item[$primaryKey])) {
                    $items[] = $item;
                    $fetchIds[] = $item[$primaryKey];
                }
            }

            // Get ids that not exist in cache handler.
            $targetIds = array_diff($ids, $fetchIds);
            $models = $instance->newQuery()->whereIn($primaryKey, $targetIds)->get();
            /** @var Model $model */
            foreach ($models as $model) {
                $id = $model->getKey();
                $key = $this->getCacheKey($id, $instance, $handler->getConfig());
                $handler->set($key, $model->toArray());
            }

            $items = array_merge($items, $models->toArray());
            $map = [];
            foreach ($items as $item) {
                $map[$item[$primaryKey]] = $item;
            }

            $result = [];
            foreach ($ids as $id) {
                if (isset($map[$id])) {
                    $result[] = $map[$id];
                }
            }

            return $instance->hydrate($result);
        }

        $this->logger->alert('Cache handler not exist, fetch data from database.');
        return $instance->newQuery()->whereIn($primaryKey, $ids)->get();
    }

    /**
     * Destroy the models for the given IDs from cache.
     */
    public function destroy($ids, string $class)
    {
        /** @var Model $instance */
        $instance = new $class();

        $name = $instance->getConnectionName();
        if ($handler = $this->handlers[$name] ?? null) {
            $keys = [];
            foreach ($ids as $id) {
                $keys[] = $this->getCacheKey($id, $instance, $handler->getConfig());
            }

            return $handler->deleteMultiple($keys);
        }

        return 0;
    }

    protected function getCacheKey($id, Model $model, Config $config)
    {
        // mc:$prefix:m:$model:$pk:$id
        return sprintf(
            $config->getCacheKey(),
            $config->getPrefix(),
            $model->getTable(),
            $model->getKeyName(),
            $id
        );
    }
}
