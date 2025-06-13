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

use Closure;
use Hyperf\CircuitBreaker\Annotation\CircuitBreaker as Annotation;
use Hyperf\CircuitBreaker\CircuitBreaker;
use Hyperf\CircuitBreaker\CircuitBreakerFactory;
use Hyperf\CircuitBreaker\CircuitBreakerInterface;
use Hyperf\CircuitBreaker\Exception\BadFallbackException;
use Hyperf\CircuitBreaker\Exception\CircuitBreakerException;
use Hyperf\CircuitBreaker\FallbackInterface;
use Hyperf\CircuitBreaker\LoggerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Throwable;

use function Hyperf\Support\make;

abstract class AbstractHandler implements HandlerInterface
{
    protected CircuitBreakerFactory $factory;

    protected ?PsrLoggerInterface $logger = null;

    public function __construct(protected ContainerInterface $container)
    {
        $this->factory = $container->get(CircuitBreakerFactory::class);
        $this->logger = match (true) {
            $container->has(LoggerInterface::class) => $container->get(LoggerInterface::class),
            $container->has(StdoutLoggerInterface::class) => $container->get(StdoutLoggerInterface::class),
            default => null,
        };
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

    protected function switch(CircuitBreakerInterface $breaker, Annotation $annotation, bool $status): void
    {
        $state = $breaker->state();
        if ($state->isClose()) {
            $this->logger?->debug('The current state is closed.');
            if ($breaker->getDuration() >= $annotation->duration) {
                $info = sprintf(
                    'The duration=%ss of closed state longer than the annotation duration=%ss and is reset to the closed state.',
                    $breaker->getDuration(),
                    $annotation->duration
                );
                $this->logger?->debug($info);
                $breaker->close();
                return;
            }

            if (! $status && $breaker->getFailCounter() >= $annotation->failCounter) {
                $info = sprintf(
                    'The failCounter=%s more than the annotation failCounter=%s and is reset to the open state.',
                    $breaker->getFailCounter(),
                    $annotation->failCounter
                );
                $this->logger?->debug($info);
                $breaker->open();
                return;
            }

            return;
        }

        if ($state->isHalfOpen()) {
            $this->logger?->debug('The current state is half opened.');
            if (! $status && $breaker->getFailCounter() >= $annotation->failCounter) {
                $info = sprintf(
                    'The failCounter=%s more than the annotation failCounter=%s and is reset to the open state.',
                    $breaker->getFailCounter(),
                    $annotation->failCounter
                );
                $this->logger?->debug($info);
                $breaker->open();
                return;
            }

            if ($status && $breaker->getSuccessCounter() >= $annotation->successCounter) {
                $info = sprintf(
                    'The successCounter=%s more than the annotation successCounter=%s and is reset to the closed state.',
                    $breaker->getSuccessCounter(),
                    $annotation->successCounter
                );
                $this->logger?->debug($info);
                $breaker->close();
                return;
            }

            return;
        }

        if ($state->isOpen()) {
            $this->logger?->debug('The current state is opened.');
            if ($breaker->getDuration() >= $annotation->duration) {
                $info = sprintf(
                    'The duration=%ss of opened state longer than the annotation duration=%ss and is reset to the half opened state.',
                    $breaker->getDuration(),
                    $annotation->duration
                );
                $this->logger?->debug($info);
                $breaker->halfOpen();
            }
        }
    }

    protected function call(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreakerInterface $breaker, Annotation $annotation)
    {
        try {
            $result = $this->process($proceedingJoinPoint, $breaker, $annotation);

            $breaker->incrSuccessCounter();
            $this->switch($breaker, $annotation, true);
        } catch (Throwable $exception) {
            if (! $exception instanceof CircuitBreakerException) {
                throw $exception;
            }

            $result = $exception->getResult();
            $msg = sprintf('%s::%s %s.', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName, $exception->getMessage());
            $this->logger?->debug($msg);

            $breaker->incrFailCounter();
            $this->switch($breaker, $annotation, false);
        }

        return $result;
    }

    protected function attemptCall(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreakerInterface $breaker, Annotation $annotation)
    {
        if ($breaker->attempt()) {
            return $this->call($proceedingJoinPoint, $breaker, $annotation);
        }

        return $this->fallback($proceedingJoinPoint, $breaker, $annotation);
    }

    protected function fallback(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreakerInterface $breaker, Annotation $annotation)
    {
        if ($annotation->fallback instanceof Closure) {
            return ($annotation->fallback)($proceedingJoinPoint);
        }
        [$class, $method] = $this->prepareHandler($annotation->fallback, $proceedingJoinPoint);

        $instance = $this->container->get($class);
        if ($instance instanceof FallbackInterface) {
            return $instance->fallback($proceedingJoinPoint);
        }

        $arguments = $proceedingJoinPoint->getArguments();

        return $instance->{$method}(...$arguments);
    }

    abstract protected function process(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreakerInterface $breaker, Annotation $annotation);

    protected function prepareHandler(array|string $fallback, ProceedingJoinPoint $proceedingJoinPoint): array
    {
        if (is_string($fallback)) {
            $fallback = explode('::', $fallback);
            if (! isset($fallback[1]) && method_exists($proceedingJoinPoint->className, $fallback[0])) {
                return [$proceedingJoinPoint->className, $fallback[0]];
            }
            $fallback[1] ??= 'fallback';
        }

        if (is_array($fallback) && count($fallback) === 2) {
            return $fallback;
        }

        throw new BadFallbackException('The fallback is invalid.');
    }
}
