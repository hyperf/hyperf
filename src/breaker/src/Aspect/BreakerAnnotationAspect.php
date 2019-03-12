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

namespace Hyperf\Breaker\Aspect;

use Hyperf\Breaker\Annotation\Breaker;
use Hyperf\Breaker\Handler\HandlerInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;

/**
 * @Aspect
 */
class BreakerAnnotationAspect extends AbstractAspect
{
    public $annotations = [
        Breaker::class,
    ];

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Breaker $annotation */
        $annotation = $metadata->method[Breaker::class] ?? null;

        if (! $annotation) {
            return $proceedingJoinPoint->process();
        }

        $handlerClass = $annotation->handler;

        if (! $this->container->has($handlerClass)) {
            return $proceedingJoinPoint->process();
        }

        /** @var HandlerInterface $handler */
        $handler = $this->container->get($handlerClass);

        return $handler->handle($proceedingJoinPoint, $annotation);
    }
}
