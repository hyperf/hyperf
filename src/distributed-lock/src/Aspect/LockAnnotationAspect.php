<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLock\Aspect;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\DistributedLock\Annotation\Lock;
use Hyperf\DistributedLock\AnnotationManager;
use Hyperf\DistributedLock\Exception\LockException;
use Hyperf\DistributedLock\LockManager;

/**
 * @Aspect
 */
class LockAnnotationAspect implements AroundInterface
{
    public $classes = [];

    public $annotations = [
        Lock::class,
    ];

    /**
     * @var array
     */
    private $annotationProperty;

    /**
     * @var array
     */
    private $config;

    /**
     * @var LockManager
     */
    protected $manager;

    /**
     * @var AnnotationManager
     */
    protected $annotationManager;

    public function __construct(LockManager $manager, AnnotationManager $annotationManager, ConfigInterface $config)
    {
        $this->manager            = $manager;
        $this->annotationManager  = $annotationManager;
        $this->annotationProperty = get_object_vars(new Lock());
        $this->config             = $config->get('distributed-lock', []);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className  = $proceedingJoinPoint->className;
        $method     = $proceedingJoinPoint->methodName;
        $arguments  = $proceedingJoinPoint->arguments['keys'];
        $driverName = $this->config['driver'] ?? 'redis';
        $separator  = $this->config[$driverName]['separator'] ?? ':';

        [$key, $ttl, $annotation] = $this->annotationManager->getLockValue($className, $method, $arguments, $separator);

        $driver = $this->manager->getDriver($driverName);

        $locker = $driver->lock($key, $ttl);
        if (!$locker) {
            if (!$annotation->failedCallback || !is_callable($annotation->failedCallback)) {
                throw new LockException('Service Unavailable.', 503);
            }

            return call_user_func($annotation->failedCallback);
        }
        try {
            return $proceedingJoinPoint->process();
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            $driver->unlock($locker);
        }
    }
}
