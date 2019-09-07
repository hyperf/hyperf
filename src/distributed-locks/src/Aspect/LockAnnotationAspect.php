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

namespace Hyperf\DistributedLocks\Aspect;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\DistributedLocks\Annotation\Lock;
use Swoole\Coroutine;

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


    public function __construct(ConfigInterface $config, RequestInterface $request, RateLimitHandler $rateLimitHandler)
    {
        $this->annotationProperty = get_object_vars(new Lock());
        $this->config             = $config->get('distributed-locks.mutex', []);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // todo 获得锁

        $locker = true;
        if (!$locker) {
            // 没有获得锁时处理
        }

        try {
            $result = $proceedingJoinPoint->process();
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            // todo 解锁
            $locker->unlock();
        }
    }

    /**
     * @param Lock[] $annotations
     */
    public function getWeightingAnnotation(array $annotations): Lock
    {
        $property = array_merge($this->annotationProperty, $this->config);
        foreach ($annotations as $annotation) {
            if (!$annotation) {
                continue;
            }
            $property = array_merge($property, array_filter(get_object_vars($annotation)));
        }

        return new Lock($property);
    }

    public function getAnnotations(ProceedingJoinPoint $proceedingJoinPoint): array
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        return [
            $metadata->method[Lock::class] ?? null,
        ];
    }
}
