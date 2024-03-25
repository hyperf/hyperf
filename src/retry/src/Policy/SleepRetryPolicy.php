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

namespace Hyperf\Retry\Policy;

use Hyperf\Retry\RetryContext;

use function Hyperf\Support\make;

class SleepRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    public function __construct(private int $base, private string $sleepStrategyClass)
    {
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        return true;
    }

    public function beforeRetry(RetryContext &$retryContext): void
    {
        $retryContext['strategy']->sleep();
    }

    public function start(RetryContext $parentRetryContext): RetryContext
    {
        $parentRetryContext['strategy'] = make($this->sleepStrategyClass, [
            'base' => $this->base,
        ]);
        return $parentRetryContext;
    }
}
