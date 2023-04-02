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

use Hyperf\Collection\Arr;
use Hyperf\Retry\RetryContext;
use Throwable;

class ClassifierRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @param callable|mixed $retryOnThrowablePredicate
     * @param callable|mixed $retryOnResultPredicate
     */
    public function __construct(
        private array $ignoreThrowables = [],
        private array $retryThrowables = [Throwable::class],
        private mixed $retryOnThrowablePredicate = '',
        private mixed $retryOnResultPredicate = ''
    ) {
    }

    public function canRetry(RetryContext &$retryContext): bool
    {
        if ($retryContext->isFirstTry()) {
            return true;
        }
        if ($retryContext['lastThrowable'] !== null) {
            return $this->isRetriable($retryContext['lastThrowable']);
        }
        if (! is_callable($this->retryOnResultPredicate)) {
            return false;
        }
        if ($retryContext['lastResult'] !== null) {
            return call_user_func($this->retryOnResultPredicate, $retryContext['lastResult']);
        }
        return false;
    }

    private function in(Throwable $t, array $arr): bool
    {
        return (bool) Arr::first($arr, fn ($v) => $t instanceof $v);
    }

    private function isRetriable(Throwable $t): bool
    {
        if ($this->in($t, $this->ignoreThrowables)) {
            return false;
        }
        if ($this->in($t, $this->retryThrowables)) {
            return true;
        }
        if (! is_callable($this->retryOnThrowablePredicate)) {
            return false;
        }
        if (call_user_func($this->retryOnThrowablePredicate, $t)) {
            return true;
        }

        return false;
    }
}
