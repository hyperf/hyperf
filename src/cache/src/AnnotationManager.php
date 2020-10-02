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
namespace Hyperf\Cache;

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Cache\Annotation\FailCache;
use Hyperf\Cache\Exception\CacheException;
use Hyperf\Cache\Helper\StringHelper;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

class AnnotationManager
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getCacheableValue(string $className, string $method, array $arguments): array
    {
        /** @var Cacheable $annotation */
        $annotation = $this->getAnnotation(Cacheable::class, $className, $method);

        $key = $this->getFormatedKey($annotation->prefix, $arguments, $annotation->value);
        $group = $annotation->group;
        $ttl = $annotation->ttl ?? $this->config->get("cache.{$group}.ttl", 3600);

        return [$key, $ttl + $this->getRandomOffset($annotation->offset), $group, $annotation];
    }

    public function getCacheEvictValue(string $className, string $method, array $arguments): array
    {
        /** @var CacheEvict $annotation */
        $annotation = $this->getAnnotation(CacheEvict::class, $className, $method);

        $prefix = $annotation->prefix;
        $all = $annotation->all;
        $group = $annotation->group;
        if (! $all) {
            $key = $this->getFormatedKey($prefix, $arguments, $annotation->value);
        } else {
            $key = $prefix . ':';
        }

        return [$key, $all, $group, $annotation];
    }

    public function getCachePutValue(string $className, string $method, array $arguments): array
    {
        /** @var CachePut $annotation */
        $annotation = $this->getAnnotation(CachePut::class, $className, $method);

        $key = $this->getFormatedKey($annotation->prefix, $arguments, $annotation->value);
        $group = $annotation->group;
        $ttl = $annotation->ttl ?? $this->config->get("cache.{$group}.ttl", 3600);

        return [$key, $ttl + $this->getRandomOffset($annotation->offset), $group, $annotation];
    }

    public function getFailCacheValue(string $className, string $method, array $arguments): array
    {
        /** @var FailCache $annotation */
        $annotation = $this->getAnnotation(FailCache::class, $className, $method);

        $prefix = $annotation->prefix ?? ($className . '::' . $method);
        $key = $this->getFormatedKey($prefix, $arguments, $annotation->value);
        $group = $annotation->group;
        $ttl = $annotation->ttl ?? $this->config->get("cache.{$group}.ttl", 3600);

        return [$key, $ttl, $group, $annotation];
    }

    protected function getRandomOffset(int $offset): int
    {
        if ($offset > 0) {
            return rand(0, $offset);
        }

        return 0;
    }

    protected function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $collector = AnnotationCollector::get($className);
        $result = $collector['_m'][$method][$annotation] ?? null;
        if (! $result instanceof $annotation) {
            throw new CacheException(sprintf('Annotation %s in %s:%s not exist.', $annotation, $className, $method));
        }

        return $result;
    }

    protected function getFormatedKey(string $prefix, array $arguments, ?string $value = null): string
    {
        $key = StringHelper::format($prefix, $arguments, $value);

        if (strlen($key) > 64) {
            $this->logger->warning('The cache key length is too long. The key is ' . $key);
        }

        return $key;
    }
}
