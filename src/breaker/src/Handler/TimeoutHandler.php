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
use Hyperf\Breaker\CircuitBreaker\CircuitBreaker;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class TimeoutHandler extends AbstractHandler
{
    protected function call(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Breaker $annotation)
    {
        $timeout = $annotation->timeout;
        $time = microtime(true);
        $result = parent::call($proceedingJoinPoint, $breaker, $annotation);
        if (microtime(true) - $time > $timeout) {
            $breaker->state()->open();
            $err = sprintf(
                'Call %s@%s timeout, then call it in fallback.',
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName
            );
            $this->logger->error($err);
        }

        return $result;
    }

    protected function attemptCall(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Breaker $annotation)
    {
        return $this->call($proceedingJoinPoint, $breaker, $annotation);
    }

    protected function fallback(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Breaker $annotation)
    {
        return null;
    }
}
