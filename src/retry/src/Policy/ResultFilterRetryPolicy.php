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

class ResultFilterRetryPolicy implements RetryPolicyInterface
{
    private $ignoredThrowables;
    private $retryThrowables;
    private $retryOnThrowablePredicate;
    private $retryOnResultPredicate;

    public function __construct(
        array $ignoredThrowables,
        array $retryThrowables,
        callable $retryOnThrowablePredicate,
        callable $retryOnResultPredicate
    ) {
        $this->ignoredThrowables = $ignoredThrowables;
        $this->retryThrowables = $retryThrowables;
        $this->retryOnThrowablePredicate = $retryOnThrowablePredicate;
        $this->retryOnResultPredicate = $retryOnResultPredicate;
    }
    
    public function canRetry(array $retryContext): bool
    {
        if (isset($retryContext['last_throwable'])) {
            return $this->isRetriable($retryContext['last_throwable']);
        }
        if (isset($retryContext['last_result'])) {
            return call_user_func($this->retryOnResultPredicate, $retryContext['last_result']);
        }
        return true;
    }

    public function start(array $parentRetryContext = []): array
    {
        $parentRetryContext['last_result'] = null;
        $parentRetryContext['last_throwable'] = null;
        return $parentRetryContext;
    }

    public function break(array $retryContext): void
    {
        //no op
    }

    public function registerResult(array &$retryContext, $result, ?Throwable $throwable = null)
    {
        $retryContext['last_result'] = $result;
        $parentRetryContext['last_throwable'] = $throwable;
    }

    private function in(\Throwable $t, array $arr): bool
    {
        return Arr::first(
            $arr,
            function ($v) use ($t) {
                return $t instanceof $v;
            }
        ) ? true : false;
    }

    private function isRetriable(\Throwable $t): bool
    {
        if ($this->in($t, $this->ignoreThrowables)) {
            return false;
        }

        if ($this->in($t, $this->retryThrowables)) {
            return true;
        }

        if (call_user_func($this->retryOnThrowablePredicate, $t)) {
            return true;
        }

        return false;
    }
}
