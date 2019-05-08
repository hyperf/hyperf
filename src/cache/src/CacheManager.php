<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Cache;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Cache\Exception\CacheException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\Container\ContainerInterface;
use function call;

class CacheManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $config;

    protected $drivers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->config = $container->get(ConfigInterface::class)->get('cache', []);
    }

    public function getDriver($name = 'default'): DriverInterface
    {
        if (isset($this->drivers[$name]) && $this->drivers[$name] instanceof DriverInterface) {
            return $this->drivers[$name];
        }

        if (! isset($this->config[$name]) || ! $this->config[$name]) {
            return false;
        }

        $driverClass = $this->config[$name]['driver'] ?? RedisDriver::class;
        $driver = new $driverClass($this->container, $this->config[$name]);

        return $this->drivers[$name] = $driver;
    }

    public function call($callback, string $key, int $ttl = 3600, $config = 'default')
    {
        $driver = $this->getDriver($config);

        [$has, $result] = $driver->fetch($key);
        if ($has) {
            return $result;
        }

        $result = call($callback);
        $driver->set($key, $result, $ttl);

        return $result;
    }

    public function getAnnotationValue(string $className, string $method, array $arguments)
    {
        /** @var Cacheable $annotation */
        $annotation = $this->getAnnotation(Cacheable::class, $className, $method);

        $key = $annotation->key;
        $key = $this->formatKey($key, $arguments);
        $group = $annotation->group ?? 'default';
        $ttl = $annotation->ttl ?? $this->config[$group]['ttl'] ?? 3600;

        return [$key, $ttl, $group];
    }

    public function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $collector = AnnotationCollector::get($className);
        $result = $collector['_m'][$method][$annotation] ?? null;
        if (! $result instanceof $annotation) {
            throw new CacheException(sprintf('Annotation %s in %s:%s not exist.', $annotation, $className, $method));
        }

        return $result;
    }

    public function formatKey($key, array $arguments)
    {
        $hasObject = false;
        foreach ($arguments as $argument) {
            if (is_object($argument)) {
                $hasObject = true;
                break;
            }
        }

        if ($hasObject) {
            $key .= ':' . md5(serialize($arguments));
        } else {
            $key .= ':' . implode(':', $arguments);
        }

        if (strlen($key) > 64) {
            $key = 'cache:' . md5($key);
        }

        return $key;
    }
}
