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

class MaxAttemptsRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var int
     */
    private $maxAttempts;

    public function __construct(int $maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    public function canRetry(array &$retryContext): bool
    {
        if ($this->isFirstTry($retryContext)) {
            return true;
        }
        if ($retryContext['attempt'] < $this->maxAttempts) {
            return true;
        }
        $retryContext['retry_exhausted'] = true;
        return false;
    }

    public function start(array $parentRetryContext = []): array
    {
        $parentRetryContext['attempt'] = 1;
        return $parentRetryContext;
    }

    public function beforeRetry(array &$retryContext): void
    {
        ++$retryContext['attempt'];
    }
}
