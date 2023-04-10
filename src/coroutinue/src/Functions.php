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
namespace Hyperf\Coroutine;

use Closure;
use Hyperf\Context\ApplicationContext;

/**
 * @param callable[] $callables
 * @param int $concurrent if $concurrent is equal to 0, that means unlimited
 */
function parallel(array $callables, int $concurrent = 0)
{
    $parallel = new Parallel($concurrent);
    foreach ($callables as $key => $callable) {
        $parallel->add($callable, $key);
    }
    return $parallel->wait();
}

function wait(Closure $closure, ?float $timeout = null)
{
    if (ApplicationContext::hasContainer()) {
        $waiter = ApplicationContext::getContainer()->get(Waiter::class);
        return $waiter->wait($closure, $timeout);
    }
    return (new Waiter())->wait($closure, $timeout);
}

/**
 * @return bool|int
 */
function co(callable $callable)
{
    $id = Coroutine::create($callable);
    return $id > 0 ? $id : false;
}

function defer(callable $callable): void
{
    Coroutine::defer($callable);
}

/**
 * @return bool|int
 */
function go(callable $callable)
{
    $id = Coroutine::create($callable);
    return $id > 0 ? $id : false;
}
