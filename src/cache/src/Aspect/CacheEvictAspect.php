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

use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\KeyCollectorInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class CacheEvictAspect extends AbstractAspect
{
    public $annotations = [
        CacheEvict::class,
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

        [$key, $all, $group, $annotation] = $this->annotationManager->getCacheEvictValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        if ($all) {
            if ($driver instanceof KeyCollectorInterface && $annotation instanceof CacheEvict && $annotation->collect) {
                $collector = $annotation->prefix . 'MEMBERS';
                $keys = $driver->keys($collector);
                if ($keys) {
                    $driver->deleteMultiple($keys);
                    $driver->delete($collector);
                }
            } else {
                $driver->clearPrefix($key);
            }
        } else {
            $driver->delete($key);
        }

        return $proceedingJoinPoint->process();
    }
}
