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

namespace Hyperf\Retry;

class Retry
{
    public static function __callStatic($method, $arguments)
    {
        $retry = new FluentRetry();
        return $retry->{$method}(...$arguments);
    }
}
