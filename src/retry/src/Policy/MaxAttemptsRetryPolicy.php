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

    public function canRetry(RetryContext &$retryContext): bool
    {
        if ($retryContext->isFirstTry()) {
            return true;
        }
        if ($retryContext['attempt'] < $this->maxAttempts) {
            return true;
        }
        $retryContext['retryExhausted'] = true;
        return false;
    }

    public function start(RetryContext $parentRetryContext): RetryContext
    {
        $parentRetryContext['attempt'] = 1;
        return $parentRetryContext;
    }

    public function beforeRetry(RetryContext &$retryContext): void
    {
        ++$retryContext->attempt;
    }
}
