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

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\KeyCollectorInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class CacheableAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        Cacheable::class,
    ];

    public function __construct(protected CacheManager $manager, protected AnnotationManager $annotationManager)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        [$key, $ttl, $group, $annotation] = $this->annotationManager->getCacheableValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        [$has, $result] = $driver->fetch($key);
        if ($has) {
            return $result;
        }

        $result = $proceedingJoinPoint->process();

        if (! in_array($result, (array) $annotation->skipCacheResults, true)) {
            $driver->set($key, $result, $ttl);

            if ($driver instanceof KeyCollectorInterface && $annotation instanceof Cacheable && $annotation->collect) {
                $driver->addKey($annotation->prefix . 'MEMBERS', $key);
            }
        }

        return $result;
    }
}
