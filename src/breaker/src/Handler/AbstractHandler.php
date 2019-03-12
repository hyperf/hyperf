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

namespace Hyperf\Breaker\Handler;

use Hyperf\Breaker\Annotation\Breaker;
use Hyperf\Breaker\State;
use Hyperf\Breaker\Storage\StorageInterface;
use Hyperf\Breaker\StorageFactory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var StorageFactory
     */
    protected $storageFactory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->storageFactory = $container->get(StorageFactory::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function handle(ProceedingJoinPoint $proceedingJoinPoint, Breaker $annotation)
    {
        $name = $this->getName($proceedingJoinPoint);
        /** @var StorageInterface $storage */
        $storage = $this->storageFactory->get($name);
        if (! $storage instanceof StorageInterface) {
            $class = $annotation->storage;
            $storage = make($class, ['name' => $name]);
            $this->storageFactory->set($name, $storage);
        }

        $state = $storage->getState();
        if ($state->isOpen()) {
            return $this->fallback($proceedingJoinPoint, $state, $annotation);
        }
        if ($state->isHalfOpen()) {
            return $this->halfCall($proceedingJoinPoint, $state, $annotation);
        }

        return $this->call($proceedingJoinPoint, $state, $annotation);
    }

    protected function getName(ProceedingJoinPoint $proceedingJoinPoint): string
    {
        $name = sprintf('%s@%s', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
        $arguments = $proceedingJoinPoint->getArguments();
        if (count($arguments) > 0) {
            $name .= implode(':', $arguments);
        }

        return $name;
    }

    abstract protected function call(ProceedingJoinPoint $proceedingJoinPoint, State $state, Breaker $annotation);

    abstract protected function halfCall(ProceedingJoinPoint $proceedingJoinPoint, State $state, Breaker $annotation);

    abstract protected function fallback(ProceedingJoinPoint $proceedingJoinPoint, State $state, Breaker $annotation);
}
