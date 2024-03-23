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
use Hyperf\Cache\Annotation\CacheAhead;
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
    public function __construct(protected ConfigInterface $config, protected StdoutLoggerInterface $logger)
    {
    }

    public function getCacheableValue(string $className, string $method, array $arguments): array
    {
        /** @var Cacheable $annotation */
        $annotation = $this->getAnnotation(Cacheable::class, $className, $method);

        $key = $this->getFormattedKey($annotation->prefix, $arguments, $annotation->value);
        $group = $annotation->group;
        $ttl = $annotation->ttl ?? $this->config->get("cache.{$group}.ttl", 3600);
        $annotation->skipCacheResults ??= (array) $this->config->get("cache.{$group}.skip_cache_results", []);

        return [$key, $ttl + $this->getRandomOffset($annotation->offset), $group, $annotation];
    }

    public function getCacheAheadValue(string $className, string $method, array $arguments): array
    {
        /** @var CacheAhead $annotation */
        $annotation = $this->getAnnotation(CacheAhead::class, $className, $method);

        $key = $this->getFormattedKey($annotation->prefix, $arguments, $annotation->value);
        $group = $annotation->group;
        $ttl = $annotation->ttl ?? $this->config->get("cache.{$group}.ttl", 3600);
        $annotation->skipCacheResults ??= (array) $this->config->get("cache.{$group}.skip_cache_results", []);

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
            $key = $this->getFormattedKey($prefix, $arguments, $annotation->value);
        } else {
            $key = $prefix . ':';
        }

        return [$key, $all, $group, $annotation];
    }

    public function getCachePutValue(string $className, string $method, array $arguments): array
    {
        /** @var CachePut $annotation */
        $annotation = $this->getAnnotation(CachePut::class, $className, $method);

        $key = $this->getFormattedKey($annotation->prefix, $arguments, $annotation->value);
        $group = $annotation->group;
        $ttl = $annotation->ttl ?? $this->config->get("cache.{$group}.ttl", 3600);
        $annotation->skipCacheResults ??= (array) $this->config->get("cache.{$group}.skip_cache_results", []);

        return [$key, $ttl + $this->getRandomOffset($annotation->offset), $group, $annotation];
    }

    public function getFailCacheValue(string $className, string $method, array $arguments): array
    {
        /** @var FailCache $annotation */
        $annotation = $this->getAnnotation(FailCache::class, $className, $method);

        $prefix = $annotation->prefix ?? ($className . '::' . $method);
        $key = $this->getFormattedKey($prefix, $arguments, $annotation->value);
        $group = $annotation->group;
        $ttl = $annotation->ttl ?? $this->config->get("cache.{$group}.ttl", 3600);
        $annotation->skipCacheResults ??= (array) $this->config->get("cache.{$group}.skip_cache_results", []);

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

    protected function getFormattedKey(string $prefix, array $arguments, ?string $value = null): string
    {
        return StringHelper::format($prefix, $arguments, $value);
    }
}
