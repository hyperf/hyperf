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

namespace Hyperf\CircuitBreaker\Handler;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker as Annotation;
use Hyperf\CircuitBreaker\CircuitBreaker;
use Hyperf\CircuitBreaker\CircuitBreakerFactory;
use Hyperf\CircuitBreaker\CircuitBreakerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var CircuitBreakerFactory
     */
    protected $factory;

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
        $this->factory = $container->get(CircuitBreakerFactory::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function handle(ProceedingJoinPoint $proceedingJoinPoint, Annotation $annotation)
    {
        $name = $this->getName($proceedingJoinPoint);
        /** @var CircuitBreakerInterface $breaker */
        $breaker = $this->factory->get($name);
        if (! $breaker instanceof CircuitBreakerInterface) {
            $breaker = make(CircuitBreaker::class, ['name' => $name]);
            $this->factory->set($name, $breaker);
        }

        $state = $breaker->state();
        if ($state->isOpen()) {
            $this->switch($breaker, $annotation, false);
            return $this->fallback($proceedingJoinPoint, $breaker, $annotation);
        }
        if ($state->isHalfOpen()) {
            return $this->attemptCall($proceedingJoinPoint, $breaker, $annotation);
        }

        return $this->call($proceedingJoinPoint, $breaker, $annotation);
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

    protected function switch(CircuitBreaker $breaker, Annotation $annotation, bool $status)
    {
        $state = $breaker->state();
        if ($state->isClose()) {
            $this->logger->debug('The current state is closed.');
            if ($breaker->getDuration() > $annotation->duration) {
                $info = sprintf(
                    'The duration=%ss of closed state longer than the annotation duration=%ss and is reset to the closed state.',
                    $breaker->getDuration(),
                    $annotation->duration
                );
                $this->logger->debug($info);
                return $breaker->close();
            }

            if (! $status && $breaker->getFailCounter() > $annotation->failCounter) {
                $info = sprintf(
                    'The failCounter=%s more than the annotation failCounter=%s and is reset to the open state.',
                    $breaker->getFailCounter(),
                    $annotation->failCounter
                );
                $this->logger->debug($info);
                return $breaker->open();
            }

            return;
        }

        if ($state->isHalfOpen()) {
            $this->logger->debug('The current state is half opened.');
            if (! $status && $breaker->getFailCounter() > $annotation->failCounter) {
                $info = sprintf(
                    'The failCounter=%s more than the annotation failCounter=%s and is reset to the open state.',
                    $breaker->getFailCounter(),
                    $annotation->failCounter
                );
                $this->logger->debug($info);
                return $breaker->open();
            }

            if ($status && $breaker->getSuccessCounter() > $annotation->successCounter) {
                $info = sprintf(
                    'The successCounter=%s more than the annotation successCounter=%s and is reset to the closed state.',
                    $breaker->getSuccessCounter(),
                    $annotation->successCounter
                );
                $this->logger->debug($info);
                return $breaker->close();
            }

            return;
        }

        if ($state->isOpen()) {
            $this->logger->debug('The current state is opened.');
            if ($breaker->getDuration() > $annotation->duration) {
                $info = sprintf(
                    'The duration=%ss of opened state longer than the annotation duration=%ss and is reset to the half opened state.',
                    $breaker->getDuration(),
                    $annotation->duration
                );
                $this->logger->debug($info);
                return $breaker->halfOpen();
            }

            return;
        }
    }

    protected function call(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        return $proceedingJoinPoint->process();
    }

    protected function attemptCall(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        if ($breaker->attempt()) {
            return $this->call($proceedingJoinPoint, $breaker, $annotation);
        }

        return $this->fallback($proceedingJoinPoint, $breaker, $annotation);
    }

    abstract protected function fallback(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation);
}
