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
                throw new TimeoutException('timeout, use ' . $use . 's');
            }

            $msg = sprintf('%s@%s success, use %ss.', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName, $use);
            $this->logger->debug($msg);

            $breaker->incSuccessCounter();
            $this->switch($breaker, $annotation, true);
        } catch (\Throwable $exception) {
            if (! $exception instanceof CircuitBreakerException) {
                throw $exception;
            }

            $msg = sprintf('%s@%s %s.', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName, $exception->getMessage());
            $this->logger->debug($msg);

            $breaker->incFailCounter();
            $this->switch($breaker, $annotation, false);
        }

        return $result;
    }
}
