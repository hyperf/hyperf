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

interface RetryPolicyInterface
{
    /**
     * Test if we can retry. Note this also includes the first
     * try.
     * @param RetryContext $retryContext the current status object
     * @return bool true if the operation can proceed
     */
    public function canRetry(RetryContext &$retryContext): bool;

    /**
     * Acquire resources needed for the retry session.
     * @param RetryContext $parentRetryContext the parent status object
     * @return RetryContext $parentRetryContext the current status object
     */
    public function start(RetryContext $parentRetryContext): RetryContext;

    /**
     * Update context or take action before retry.
     * @param RetryContext $retryContext the current status object
     */
    public function beforeRetry(RetryContext &$retryContext): void;

    /**
     * Define what would happen when the retry session ultimately failed.
     * @param RetryContext $retryContext the current status object
     * @return bool whether the policy chain should continue
     */
    public function end(RetryContext &$retryContext): bool;
}
