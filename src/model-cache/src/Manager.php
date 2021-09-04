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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Collector\TableCollector;
use Hyperf\ModelCache\Handler\HandlerInterface;
use Hyperf\ModelCache\Handler\RedisHandler;
use InvalidArgumentException;
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

    /**
     * @var TableCollector
     */
    protected $collector;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->collector = $container->get(TableCollector::class);

        $config = $container->get(ConfigInterface::class);
        if (! $config->has('databases')) {
            throw new InvalidArgumentException('config databases is not exist!');
        }

        foreach ($config->get('databases') as $key => $item) {
            $handlerClass = $item['cache']['handler'] ?? RedisHandler::class;
            $config = new Config($item['cache'] ?? [], $key);

            /** @var HandlerInterface $handler */
            $handler = make($handlerClass, ['config' => $config]);

            $this->handlers[$key] = $handler;
        }
    }

    /**
     * Fetch a model from cache.
     * @param mixed $id
     */
    public function findFromCache($id, string $class): ?Model
    {
        /** @var Model $instance */
        $instance = new $class();

        $name = $instance->getConnectionName();
        $primaryKey = $instance->getKeyName();

        if ($handler = $this->handlers[$name] ?? null) {
            $key = $this->getCacheKey($id, $instance, $handler->getConfig());
            $data = $handler->get($key);
            if ($data) {
                return $instance->newFromBuilder(
                    $this->getAttributes($handler->getConfig(), $instance, $data)
                );
            }

            // Fetch it from database, because it not exist in cache handler.
            if (is_null($data)) {
                $model = $instance->newQuery()->where($primaryKey, '=', $id)->first();
                if ($model) {
                    $ttl = $this->getCacheTTL($instance, $handler);
                    $handler->set($key, $this->formatModel($model), $ttl);
                } else {
                    $ttl = $handler->getConfig()->getEmptyModelTtl();
                    $handler->set($key, [], $ttl);
                }
                return $model;
            }

            // It not exist in cache handler and database.
            return null;
        }

        $this->logger->alert('Cache handler not exist, fetch data from database.');
        return $instance->newQuery()->where($primaryKey, '=', $id)->first();
    }

    /**
     * Fetch many models from cache.
     */
    public function findManyFromCache(array $ids, string $class): Collection
    {
        if (count($ids) === 0) {
            return new Collection([]);
        }

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
            if ($targetIds) {
                $models = $instance->newQuery()->whereIn($primaryKey, $targetIds)->get();
                $ttl = $this->getCacheTTL($instance, $handler);
                /** @var Model $model */
                foreach ($models as $model) {
                    $id = $model->getKey();
                    $key = $this->getCacheKey($id, $instance, $handler->getConfig());
                    $handler->set($key, $this->formatModel($model), $ttl);
                }

                $items = array_merge($items, $this->formatModels($models));
            }
            $map = [];
            foreach ($items as $item) {
                $map[$item[$primaryKey]] = $this->getAttributes($handler->getConfig(), $instance, $item);
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
        // @phpstan-ignore-next-line
        return $instance->newQuery()->whereIn($primaryKey, $ids)->get();
    }

    /**
     * Destroy the models for the given IDs from cache.
     * @param mixed $ids
     */
    public function destroy($ids, string $class): bool
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

        return false;
    }

    /**
     * Increment a column's value by a given amount.
     * @param mixed $id
     * @param mixed $column
     * @param mixed $amount
     */
    public function increment($id, $column, $amount, string $class): bool
    {
        /** @var Model $instance */
        $instance = new $class();

        $name = $instance->getConnectionName();
        if ($handler = $this->handlers[$name] ?? null) {
            $key = $this->getCacheKey($id, $instance, $handler->getConfig());
            if ($handler->has($key)) {
                return $handler->incr($key, $column, $amount);
            }

            return false;
        }

        $this->logger->alert('Cache handler not exist, increment failed.');
        return false;
    }

    /**
     * @return \DateInterval|int
     */
    protected function getCacheTTL(Model $instance, HandlerInterface $handler)
    {
        if ($instance instanceof CacheableInterface) {
            return $instance->getCacheTTL() ?? $handler->getConfig()->getTtl();
        }
        return $handler->getConfig()->getTtl();
    }

    /**
     * @param int|string $id
     */
    protected function getCacheKey($id, Model $model, Config $config): string
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

    protected function formatModel(Model $model): array
    {
        return $model->getAttributes();
    }

    protected function formatModels($models): array
    {
        $result = [];
        foreach ($models as $model) {
            $result[] = $this->formatModel($model);
        }

        return $result;
    }

    protected function getAttributes(Config $config, Model $model, array $data)
    {
        if (! $config->isUseDefaultValue()) {
            return $data;
        }

        $connection = $model->getConnectionName();
        $defaultData = $this->collector->getDefaultValue(
            $connection,
            $this->getPrefix($connection) . $model->getTable()
        );
        return array_replace($defaultData, $data);
    }

    protected function getPrefix(string $connection): string
    {
        return (string) $this->container->get(ConfigInterface::class)->get('databases.' . $connection . '.prefix');
    }
}
