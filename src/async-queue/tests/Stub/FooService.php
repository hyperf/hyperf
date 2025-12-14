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

namespace HyperfTest\AsyncQueue\Stub;

use Hyperf\Context\Context;

class FooService
{
    public function test()
    {
        Context::set(self::class . '::test', 1);
    }

    protected function foo()
    {
        Context::set(self::class . '::foo', 1);
    }
}
