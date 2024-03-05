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
namespace HyperfTest\Di\Stub\LazyLoad;

class FooImplLazyLoad implements FooInterface
{
    public function bar()
    {
        return __FUNCTION__;
    }

    public function foo()
    {
        return __FUNCTION__;
    }
}
