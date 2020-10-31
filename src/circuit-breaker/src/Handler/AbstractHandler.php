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
namespace Hyperf\CircuitBreaker\Handler;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker as Annotation;
use Hyperf\CircuitBreaker\CircuitBreaker;
use Hyperf\CircuitBreaker\CircuitBreakerFactory;
use Hyperf\CircuitBreaker\CircuitBreakerInterface;
use Hyperf\CircuitBreaker\Exception\CircuitBreakerException;
use Hyperf\CircuitBreaker\FallbackInterface;
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
        return sprintf('%s::%s', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
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
        try {
            $result = $this->process($proceedingJoinPoint, $breaker, $annotation);

            $breaker->incSuccessCounter();
            $this->switch($breaker, $annotation, true);
        } catch (\Throwable $exception) {
            if (! $exception instanceof CircuitBreakerException) {
                throw $exception;
            }

            $result = $exception->getResult();
            $msg = sprintf('%s::%s %s.', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName, $exception->getMessage());
            $this->logger->debug($msg);

            $breaker->incFailCounter();
            $this->switch($breaker, $annotation, false);
        }

        return $result;
    }

    protected function attemptCall(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        if ($breaker->attempt()) {
            return $this->call($proceedingJoinPoint, $breaker, $annotation);
        }

        return $this->fallback($proceedingJoinPoint, $breaker, $annotation);
    }

    protected function fallback(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        [$className, $methodName] = $this->prepareHandler($annotation->fallback);

        $class = $this->container->get($className);
        if ($class instanceof FallbackInterface) {
            return $class->fallback($proceedingJoinPoint);
        }

        $argument = $proceedingJoinPoint->getArguments();

        return $class->{$methodName}(...$argument);
    }

    abstract protected function process(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation);

    protected function prepareHandler(string $fallback): array
    {
        $result = explode('::', $fallback);

        return [
            $result[0],
            $result[1] ?? 'fallback',
        ];
    }
}
