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
use Hyperf\CircuitBreaker\Exception\CircuitBreakerException;
use Hyperf\CircuitBreaker\Exception\TimeoutException;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class TimeoutHandler extends AbstractHandler
{
    const DEFAULT_TIMEOUT = 5;

    protected function call(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        $timeout = $annotation->value['timeout'] ?? self::DEFAULT_TIMEOUT;
        $time = microtime(true);

        try {
            $result = parent::call($proceedingJoinPoint, $breaker, $annotation);

            $use = microtime(true) - $time;
            if ($use > $timeout) {
                throw new TimeoutException('execute timeout, use ' . $use . ' s');
            }

            $breaker->incSuccessCounter();
            $this->switch($breaker, $annotation, true);
        } catch (\Throwable $exception) {
            if (! $exception instanceof CircuitBreakerException) {
                throw $exception;
            }

            $err = sprintf(
                'Call %s@%s %s, then call it in fallback.',
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName,
                $exception->getMessage()
            );

            $this->logger->error($err);

            $breaker->incFailCounter();
            $this->switch($breaker, $annotation, false);
        }

        return $result;
    }

    protected function fallback(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        return null;
    }
}
