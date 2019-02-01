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

namespace Hyperf\Cache;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\Container\ContainerInterface;

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

    public function getDriver($name)
    {
        if (isset($this->drivers[$name]) && $this->drivers[$name] instanceof DriverInterface) {
            return $this->drivers[$name];
        }

        if (! $this->config[$name]) {
            return false;
        }

        $driverClass = $this->config[$name]['driver'] ?? RedisDriver::class;
        $driver = new $driverClass($this->container, $this->config[$name]);

        return $this->drivers[$name] = $driver;
    }

    public function getAnnotationValue(string $className, string $method, array $arguments)
    {
        $collector = AnnotationCollector::get($className);
        $config = $collector['_m'][$method][Cacheable::class] ?? [];
        $key = $config['key'];
        $key = $this->formatKey($key, $arguments);
        $group = $config['group'] ?? 'default';
        $ttl = $config['ttl'] ?? $this->config[$group]['ttl'] ?? 3600;

        return [$key, $ttl, $group];
    }

    protected function formatKey($key, array $arguments)
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
