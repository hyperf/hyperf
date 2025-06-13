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

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\Di\Aop\ProceedingJoinPoint;

interface HandlerInterface
{
    public function handle(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $annotation);
}
