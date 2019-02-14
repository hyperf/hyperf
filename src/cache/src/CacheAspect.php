<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Cache;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ArroundInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Cache\Driver\DriverInterface;

/**
 * @Aspect
 */
class CacheAspect implements ArroundInterface
{
    public $classes = [];

    public $annotations = [
        Cacheable::class,
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        $manager = $this->container->get(CacheManager::class);
        [$key, $ttl, $group] = $manager->getAnnotationValue($className, $method, $arguments);

        /** @var DriverInterface $driver */
        $driver = $manager->getDriver($group);

        if ($driver->has($key)) {
            return $driver->get($key);
        }

        $result = $proceedingJoinPoint->process();

        $driver->set($key, $result, $ttl);

        return $result;
    }
}
