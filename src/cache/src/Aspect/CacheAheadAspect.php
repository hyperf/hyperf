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

namespace Hyperf\Cache\Aspect;

use Hyperf\Cache\Annotation\CacheAhead;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\KeyCollectorInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class CacheAheadAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        CacheAhead::class,
    ];

    public function __construct(protected CacheManager $manager, protected AnnotationManager $annotationManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $now = time();

        [$key, $ttl, $group, $annotation] = $this->annotationManager->getCacheAheadValue($className, $method, $arguments);
        $driver = $this->manager->getDriver($group);

        $callback = static function () use ($proceedingJoinPoint, $driver, $annotation, $key, $now, $ttl) {
            $result = $proceedingJoinPoint->process();

            if (! in_array($result, (array) $annotation->skipCacheResults, true)) {
                $driver->set(
                    $key,
                    [
                        'expired_time' => $now + $ttl - $annotation->aheadSeconds,
                        'data' => $result,
                    ],
                    $ttl
                );

                if ($driver instanceof KeyCollectorInterface && $annotation instanceof CacheAhead && $annotation->collect) {
                    $driver->addKey($annotation->prefix . 'MEMBERS', $key);
                }
            }

            return $result;
        };

        [$has, $result] = $driver->fetch($key);

        // If the cache exists, return it directly.
        if ($has && isset($result['expired_time'], $result['data'])) {
            if (
                $now > $result['expired_time']
                && $driver->getConnection()->set($key . ':lock', '1', ['NX', 'EX' => $annotation->lockSeconds])
            ) { // If the cache is about to expire, refresh the cache.
                Coroutine::create($callback);
            }

            return $result['data'];
        }

        // If the cache does not exist, execute the callback and cache the result.
        return $callback();
    }
}
