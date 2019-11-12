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

class LimitedAttemptsRetryPolicy implements RetryPolicyInterface
{
    private $maxAttempts;

    public function __construct(int $maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }
    
    public function canRetry(array $retryContext): bool
    {
        return $retryContext['attempt'] < $this->maxAttempts;
    }

    public function start(array $parentRetryContext = []): array
    {
        $parentRetryContext['attempt'] = 1;
        return $parentRetryContext;
    }

    public function break(array $retryContext): void
    {
        //no op
    }

    public function registerResult(array &$retryContext, $result, ?Throwable $throwable)
    {
        $retryContext['attempt']++;
    }
}
