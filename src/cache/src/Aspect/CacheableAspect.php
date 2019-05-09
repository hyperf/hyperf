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

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;

/**
 * @Aspect
 */
class CacheableAspect extends AbstractAspect
{
    public $classes = [];

    public $annotations = [
        Cacheable::class,
    ];

    /**
     * @var ContainerInterface
     */
    protected $manager;

    /**
     * @var AnnotationManager
     */
    protected $annotationManager;

    public function __construct(CacheManager $manager, AnnotationManager $annotationManager)
    {
        $this->manager = $manager;
        $this->annotationManager = $annotationManager;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        [$key, $ttl, $group] = $this->annotationManager->getCacheableValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        [$has, $result] = $driver->fetch($key);
        if ($has) {
            return $result;
        }

        $result = $proceedingJoinPoint->process();

        $driver->set($key, $result, $ttl);

        return $result;
    }
}
