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

namespace HyperfTest\CircuitBreaker\Stub;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker as Annotation;
use Hyperf\CircuitBreaker\CircuitBreakerInterface;
use Hyperf\CircuitBreaker\Handler\AbstractHandler;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class HandleStub extends AbstractHandler
{
    protected function process(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreakerInterface $breaker, Annotation $annotation)
    {
        return 'processed';
    }
}
