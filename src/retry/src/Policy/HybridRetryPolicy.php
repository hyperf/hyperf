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

use Hyperf\Utils\Collection;

class HybridRetryPolicy implements RetryPolicyInterface
{
    /**
     * A collection of policies.
     * @var Collection
     */
    private $policyCollection;

    public function __construct(RetryPolicyInterface ...$policies)
    {
        $this->policyCollection = new Collection($policies);
    }

    public function canRetry(array &$retryContext): bool
    {
        return $this->policyCollection
            ->every(function ($policy) use (&$retryContext) {
                return $policy->canRetry($retryContext);
            });
    }

    public function start(array $parentRetryContext = []): array
    {
        return $this->policyCollection
            ->reduce(function ($context, $policy) {
                return $policy->start($context);
            }, $parentRetryContext);
    }

    public function beforeRetry(array &$retryContext): void
    {
        $this->policyCollection
            ->each(function ($policy) use (&$retryContext) {
                return $policy->beforeRetry($retryContext);
            });
    }

    public function end(array &$retryContext): bool
    {
        return $this->policyCollection
            ->first(function ($policy) use (&$retryContext) {
                return $policy->end($retryContext);
            }) === null;
    }
}
