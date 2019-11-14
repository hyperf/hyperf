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

use Hyperf\Utils\Arr;

class ClassifierRetryPolicy extends BaseRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var string[]
     */
    private $ignoreThrowables;

    /**
     * @var string[]
     */
    private $retryThrowables;

    /**
     * @var callable|string
     */
    private $retryOnThrowablePredicate;

    /**
     * @var callable|string
     */
    private $retryOnResultPredicate;

    public function __construct(
        array $ignoreThrowables = [],
        array $retryThrowables = [\Throwable::class],
        $retryOnThrowablePredicate = '',
        $retryOnResultPredicate = ''
    ) {
        $this->ignoreThrowables = $ignoreThrowables;
        $this->retryThrowables = $retryThrowables;
        $this->retryOnThrowablePredicate = $retryOnThrowablePredicate;
        $this->retryOnResultPredicate = $retryOnResultPredicate;
    }

    public function canRetry(array &$retryContext): bool
    {
        if ($this->isFirstTry($retryContext)) {
            return true;
        }
        if ($retryContext['last_throwable'] !== null) {
            return $this->isRetriable($retryContext['last_throwable']);
        }
        if (! is_callable($this->retryOnResultPredicate)) {
            return false;
        }
        if ($retryContext['last_result'] !== null) {
            return call_user_func($this->retryOnResultPredicate, $retryContext['last_result']);
        }
        return false;
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
        if (! is_callable($this->retryOnThrowablePredicate)) {
            return false;
        }
        if (call_user_func($this->retryOnThrowablePredicate, $t)) {
            return true;
        }

        return false;
    }
}
