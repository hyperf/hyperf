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
     * Test if we can retry. Note this also includes the first
     * try.
     * @param array $retryContext the current status object
     * @return bool true if the operation can proceed
     */
    public function canRetry(array &$retryContext): bool;

    /**
     * Acquire resources needed for the retry session.
     * @param array $parentRetryContext the parent status object
     * @return array $parentRetryContext the current status object
     */
    public function start(array $parentRetryContext): array;

    /**
     * Update context or take action before retry.
     * @param array $retryContext the current status object
     */
    public function beforeRetry(array &$retryContext): void;

    /**
     * Define what would happen when the retry session ultimately failed.
     * @param array $retryContext the current status object
     * @return bool whether or not the policy chain should continue
     */
    public function end(array &$retryContext): bool;
}
