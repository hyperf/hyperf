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

use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class CachePutAspect extends AbstractAspect
{
    public array $annotations = [
        CachePut::class,
    ];

    public function __construct(protected CacheManager $manager, protected AnnotationManager $annotationManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        /** @var CachePut $annotation */
        [$key, $ttl, $group, $annotation] = $this->annotationManager->getCachePutValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        $result = $proceedingJoinPoint->process();

        if (! in_array($result, (array) $annotation->skipCacheResults, true)) {
            $driver->set($key, $result, $ttl);
        }

        return $result;
    }
}
