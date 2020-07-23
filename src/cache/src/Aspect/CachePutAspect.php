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

        [$key, $ttl, $group] = $this->annotationManager->getCachePutValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        $result = $proceedingJoinPoint->process();

        $driver->set($key, $result, $ttl);

        return $result;
    }
}
