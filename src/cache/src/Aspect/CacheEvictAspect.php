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

use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\CacheManager;
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

    public function __construct(CacheManager $manager)
    {
        $this->manager = $manager;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        [$key, $all, $group] = $this->getAnnotationValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        if ($all) {
            $driver->clearPrefix($key);
        } else {
            $driver->delete($key);
        }

        return $proceedingJoinPoint->process();
    }

    protected function getAnnotationValue(string $className, string $method, array $arguments)
    {
        /** @var CacheEvict $annotation */
        $annotation = $this->manager->getAnnotation(CacheEvict::class, $className, $method);

        $prefix = $annotation->prefix;
        $all = $annotation->all;
        $group = $annotation->group ?? 'default';
        if (! $all) {
            $key = $this->manager->formatKey($prefix, $arguments, $annotation->value);
        } else {
            $key = $prefix;
        }

        return [$key, $all, $group];
    }
}
