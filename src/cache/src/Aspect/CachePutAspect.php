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

namespace Hyperf\Cache\Aspect;

use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Cache\CacheManager;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class CachePutAspect extends AbstractAspect
{
    public $annotations = [
        CachePut::class,
    ];

    /**
     * @var CacheManager
     */
    protected $manager;

    public function __construct(CacheManager $manager)
    {
        $this->manager = $manager;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        [$key, $ttl, $group] = $this->getAnnotationValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        $result = $proceedingJoinPoint->process();

        $driver->set($key, $result, $ttl);

        return $result;
    }

    protected function getAnnotationValue(string $className, string $method, array $arguments)
    {
        /** @var CachePut $annotation */
        $annotation = $this->manager->getAnnotation(CachePut::class, $className, $method);

        $ttl = $annotation->ttl ?? 3600;
        $group = $annotation->group ?? 'default';
        $key = $this->manager->formatKey($annotation->key, $arguments);

        return [$key, $ttl, $group];
    }
}
