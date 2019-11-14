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

abstract class BaseRetryPolicy
{
    public function canRetry(array &$retryContext): bool
    {
        return true;
    }

    public function beforeRetry(array &$retryContext): void
    {
    }

    public function start(array $parentRetryContext = []): array
    {
        return $parentRetryContext;
    }

    public function end(array &$retryContext): bool
    {
        return false;
    }

    protected function isFirstTry(array $retryContext): bool
    {
        return ! array_key_exists('last_result', $retryContext);
    }
}
