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

class SleepRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var int
     */
    private $base;

    /**
     * @var string
     */
    private $sleepStrategyClass;

    public function __construct(int $base, string $sleepStrategyClass)
    {
        $this->base = $base;
        $this->sleepStrategyClass = $sleepStrategyClass;
    }

    public function canRetry(array &$retryContext): bool
    {
        return true;
    }

    public function beforeRetry(array &$retryContext): void
    {
        $retryContext['strategy']->sleep();
    }

    public function start(array $parentRetryContext = []): array
    {
        $parentRetryContext['strategy'] = make($this->sleepStrategyClass, [
            'base' => $this->base,
        ]);
        return parent::start($parentRetryContext);
    }
}
