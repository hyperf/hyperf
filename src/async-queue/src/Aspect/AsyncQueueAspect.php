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
namespace Hyperf\AsyncQueue\Aspect;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\AsyncQueue\AnnotationJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Environment;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;

/**
 * @Aspect
 */
class AsyncQueueAspect extends AbstractAspect
{
    public $annotations = [
        AsyncQueueMessage::class,
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $env = $this->container->get(Environment::class);
        if ($env->isAsyncQueue()) {
            $proceedingJoinPoint->process();
            return;
        }

        $class = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = [];
        $parameters = $proceedingJoinPoint->getReflectMethod()->getParameters();
        foreach ($parameters as $parameter) {
            $arg = $proceedingJoinPoint->arguments['keys'][$parameter->getName()];
            if ($parameter->isVariadic()) {
                $arguments = array_merge($arguments, $arg);
            } else {
                $arguments[] = $arg;
            }
        }

        $pool = 'default';
        $delay = 0;
        $maxAttempts = 0;

        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var AsyncQueueMessage $annotation */
        $annotation = $metadata->method[AsyncQueueMessage::class] ?? $metadata->class[AsyncQueueMessage::class] ?? null;
        if ($annotation instanceof AsyncQueueMessage) {
            $pool = $annotation->pool;
            $delay = $annotation->delay;
            $maxAttempts = $annotation->maxAttempts;
        }

        $factory = $this->container->get(DriverFactory::class);
        $driver = $factory->get($pool);

        $job = make(AnnotationJob::class, [$class, $method, $arguments, $maxAttempts]);
        $driver->push($job, $delay);
    }
}
