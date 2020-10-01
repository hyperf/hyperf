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
use Hyperf\CircuitBreaker\Exception\TimeoutException;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class TimeoutHandler extends AbstractHandler
{
    const DEFAULT_TIMEOUT = 5;

    protected function process(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        $timeout = $annotation->value['timeout'] ?? self::DEFAULT_TIMEOUT;
        $time = microtime(true);

        $result = $proceedingJoinPoint->process();

        $use = microtime(true) - $time;
        if ($use > $timeout) {
            throw new TimeoutException('timeout, use ' . $use . 's', $result);
        }

        $msg = sprintf('%s::%s success, use %ss.', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName, $use);
        $this->logger->debug($msg);

        return $result;
    }
}
