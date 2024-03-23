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

use Hyperf\Collection\Collection;
use Hyperf\Retry\RetryContext;

class HybridRetryPolicy implements RetryPolicyInterface
{
    /**
     * A collection of policies.
     */
    private Collection $policyCollection;

    public function __construct(RetryPolicyInterface ...$policies)
    {
        $this->policyCollection = new Collection($policies);
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        return $this->policyCollection
            ->every(function ($policy) use (&$retryContext) {
                return $policy->canRetry($retryContext);
            });
    }

    public function start(?RetryContext $parentRetryContext = null): RetryContext
    {
        if ($parentRetryContext === null) {
            $parentRetryContext = new RetryContext([]);
        }
        return $this->policyCollection
            ->reduce(fn ($context, $policy) => $policy->start($context), $parentRetryContext);
    }

    public function beforeRetry(RetryContext &$retryContext): void
    {
        $this->policyCollection
            ->each(function ($policy) use (&$retryContext) {
                return $policy->beforeRetry($retryContext);
            });
    }

    public function end(RetryContext &$retryContext): bool
    {
        return $this->policyCollection
            ->first(function ($policy) use (&$retryContext) {
                return $policy->end($retryContext);
            }) === null;
    }
}
