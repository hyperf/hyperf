<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Retry\Policy;

class SleepRetryPolicy implements RetryPolicyInterface
{
    private $base;

    public function __construct(int $base)
    {
        $this->base = $base;
    }
    
    public function canRetry(array $retryContext): bool
    {
        $retryContext['strategy']->sleep();
        return true;
    }

    public function start(array $parentRetryContext = []): array
    {
        $parentRetryContext['strategy'] = make(StrategyInterface::class, [
            'base' => $this->base
        ]);
        return $parentRetryContext['strategy'];
    }

    public function break(array $retryContext): void
    {
        //no op
    }

    public function registerResult(array &$retryContext, $result, ?Throwable $throwable)
    {
        //no op
    }
}
