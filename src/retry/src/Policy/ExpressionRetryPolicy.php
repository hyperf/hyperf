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

class ExpressionRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var callable
     */
    private mixed $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        return call_user_func($this->callable, $retryContext);
    }
}
