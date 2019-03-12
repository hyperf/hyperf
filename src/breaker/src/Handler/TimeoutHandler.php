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
use Hyperf\Breaker\State;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class TimeoutHandler extends AbstractHandler
{
    protected function call(ProceedingJoinPoint $proceedingJoinPoint, State $state, Breaker $annotation)
    {
        $timeout = $annotation->timeout;
        $time = microtime(true);
        $result = $proceedingJoinPoint->process();
        if (microtime(true) - $time > $timeout) {
            $state->open();
            $err = sprintf(
                'Call %s@%s timeout, then call it in fallback.',
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName
            );
            $this->logger->error($err);
        }

        return $result;
    }

    protected function halfCall(ProceedingJoinPoint $proceedingJoinPoint, State $state, Breaker $annotation)
    {
        return $this->call($proceedingJoinPoint, $state);
    }

    protected function fallback(ProceedingJoinPoint $proceedingJoinPoint, State $state, Breaker $annotation)
    {
        $state->addFallbackCount();
        return null;
    }
}
