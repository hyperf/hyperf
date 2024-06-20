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

namespace Hyperf\Retry;

use Hyperf\Retry\Policy\RetryPolicyInterface;

use function Hyperf\Support\make;

/**
 * @method static FluentRetry with(RetryPolicyInterface ...$policies)
 * @method static FluentRetry when(callable $when)
 * @method static FluentRetry whenReturns($when)
 * @method static FluentRetry whenThrows(string $when = \Throwable::class)
 * @method static FluentRetry max(int $times)
 * @method static FluentRetry inSeconds(float $seconds)
 * @method static FluentRetry fallback(callable $fallback)
 * @method static FluentRetry sleep(int $base)
 * @method static FluentRetry backoff(int $base)
 * @method static FluentRetry call(callable $callable)
 */
class Retry
{
    public static function __callStatic($method, $arguments)
    {
        $retry = make(FluentRetry::class);
        return $retry->{$method}(...$arguments);
    }
}
