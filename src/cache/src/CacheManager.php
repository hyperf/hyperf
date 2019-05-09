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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Utils\Str;
use function call;

class CacheManager
{
    protected $config;

    protected $drivers = [];

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->config = $config->get('cache', []);
        $this->logger = $logger;
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

        $driver = make($driverClass, ['config' => $this->config[$name]]);

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

        $key = $annotation->prefix;
        $key = $this->formatKey($key, $arguments, $annotation->value);
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

    public function formatKey($prefix, array $arguments, ?string $value = null)
    {
        if ($value !== null) {
            if ($matches = $this->getMatches($value)) {
                foreach ($matches as $search) {
                    $k = str_replace('#{', '', $search);
                    $k = str_replace('}', '', $k);

                    $value = Str::replaceFirst($search, (string) data_get($arguments, $k), $value);
                }
            }
            $key = $prefix . ':' . $value;
        } else {
            $key = $prefix . ':' . implode(':', $arguments);
        }

        if (strlen($key) > 64) {
            $this->logger->warning('The cache key length is too long. The key is ' . $key);
        }

        return $key;
    }

    protected function getMatches(string $value): array
    {
        preg_match_all('/\#\{[\w\.]+\}/', $value, $matches);

        return $matches[0] ?? [];
    }
}
