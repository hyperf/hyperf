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

namespace HyperfTest\Retry\Stub;

use Throwable;

class Foo
{
    public function fallback()
    {
        return 10;
    }

    public static function staticCall()
    {
        return 10;
    }

    public function fallbackWithThrowable(string $string, ?Throwable $throwable = null)
    {
        return $string . ':' . (! is_null($throwable) ? $throwable->getMessage() : 'fallback');
    }
}
