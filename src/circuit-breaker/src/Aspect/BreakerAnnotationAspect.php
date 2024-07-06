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

namespace Hyperf\CircuitBreaker\Aspect;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\CircuitBreaker\Handler\HandlerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;

class BreakerAnnotationAspect extends AbstractAspect
{
    public array $annotations = [
        CircuitBreaker::class,
    ];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var CircuitBreaker $annotation */
        $annotation = $metadata->method[CircuitBreaker::class];

        $handlerClass = $annotation->handler;

        if (! $this->container->has($handlerClass)) {
            return $proceedingJoinPoint->process();
        }

        /** @var HandlerInterface $handler */
        $handler = $this->container->get($handlerClass);

        return $handler->handle($proceedingJoinPoint, $annotation);
    }
}
