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

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\Di\Aop\ProceedingJoinPoint;

interface HandlerInterface
{
    public function handle(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $annotation);
}
