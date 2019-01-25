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
                return $instance->newFromBuilder($data);
            }

            if (is_null($data)) {
                $model = $instance->newQuery()->where($primaryKey, '=', $id)->first();
                if ($model) {
                    $handler->set($key, $model->toArray());
                } else {
                    $handler->set($key, []);
                }
                return $model;
            }

            return null;
        }

        $this->logger->warning('Cache handler not exist, fetch data from database.');
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
            $result = [];
            $fetchIds = [];
            if ($data) {
                foreach ($data as $item) {
                    if (isset($item[$primaryKey])) {
                        $result[] = $item;
                        $fetchIds[] = $item[$primaryKey];
                    }
                }
            }

            // 验证缺少哪些实体
            $targetIds = array_diff($ids, $fetchIds);
            $models = $instance->newQuery()->whereIn($primaryKey, $targetIds)->get();
            /** @var Model $model */
            foreach ($models as $model) {
                $id = $model->getKey();
                unset($targetIds['id']);

                $key = $this->getCacheKey($id, $instance, $handler->getConfig());
                $handler->set($key, $model->toArray());
            }

            foreach ($targetIds as $id) {
                $key = $this->getCacheKey($id, $instance, $handler->getConfig());
                $handler->set($key, []);
            }

            $result = array_merge($result, $models->toArray());

            return $instance->hydrate($result);
        }

        $this->logger->warning('Cache handler not exist, fetch data from database.');
        return $instance->newQuery()->where($primaryKey, '=', $id)->first();
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
