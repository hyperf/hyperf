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
namespace Hyperf\Task\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Task\Annotation\Task;
use Hyperf\Task\Task as TaskMessage;
use Hyperf\Task\TaskExecutor;
use Psr\Container\ContainerInterface;

/**
 * @Aspect
 */
class TaskAspect extends AbstractAspect
{
    public $annotations = [
        Task::class,
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
        $executor = $this->container->get(TaskExecutor::class);
        if ($executor->isTaskEnvironment()) {
            return $proceedingJoinPoint->process();
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

        $timeout = 10;
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Task $annotation */
        $annotation = $metadata->method[Task::class] ?? $metadata->class[Task::class] ?? null;
        if ($annotation instanceof Task) {
            $timeout = $annotation->timeout;
        }

        return $executor->execute(new TaskMessage([$class, $method], $arguments), $timeout);
    }
}
