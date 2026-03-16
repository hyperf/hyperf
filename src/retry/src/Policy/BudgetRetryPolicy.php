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

use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Retry\RetryContext;

class BudgetRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    public function __construct(private RetryBudgetInterface $retryBudget)
    {
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        if ($retryContext->isFirstTry()) {
            return true;
        }
        if ($this->retryBudget->consume(true)) {
            return true;
        }
        $retryContext['retryExhausted'] = true;
        return false;
    }

    public function beforeRetry(RetryContext &$retryContext): void
    {
        $this->retryBudget->consume();
    }

    public function start(RetryContext $parentRetryContext): RetryContext
    {
        $this->retryBudget->produce();
        return $parentRetryContext;
    }
}
