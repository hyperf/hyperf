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
if (! function_exists('go')) {
    function go(callable $callable): bool|int
    {
        return \Hyperf\Coroutine\go($callable);
    }
}

if (! function_exists('co')) {
    function co(callable $callable): bool|int
    {
        return \Hyperf\Coroutine\co($callable);
    }
}

if (! function_exists('defer')) {
    function defer(callable $callable): void
    {
        \Hyperf\Coroutine\defer($callable);
    }
}
