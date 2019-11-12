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

interface RetryPolicyInterface
{

    /**
     * @param array $retryContext the current status object.
     * @return true if the operation can proceed
     */
    public function canRetry(array $retryContext): bool;

    /**
     * Acquire resources needed for the retry operation. The callback is passed in so that
     * marker interfaces can be used and a manager can collaborate with the callback to
     * set up some state in the status token.
     * @param array $retryContext the parent status object.
     * @return array $retryContext the current status object.
     */
    public function start(array $parentRetryContext): array;

    /**
     * @param array $retryContext the current status object.
     */
    public function break(array $retryContext): void;

    /**
     * Called once per retry attempt, after the callback fails or success.
     * @param array $retryContext the current status object.
     * @param mixed the returned result.
     * @param Throwable|null the exception thrown.
     *
     */
    public function registerResult(array &$retryContext, $result, ?Throwable $throwable);
}
