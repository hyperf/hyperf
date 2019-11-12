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

class BudgetRetryPolicy implements RetryPolicyInterface
{
    private $budget;

    public function __construct(RetryBudgetInterface $budget)
    {
        $this->budget = $budget;
    }
    
    public function canRetry(array $retryContext): bool
    {
        return $this->budget->consume();
    }

    public function start(array $parentRetryContext = []): array
    {
        $this->budget->produce();
        return $parentRetryContext;
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
