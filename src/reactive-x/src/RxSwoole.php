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
namespace Hyperf\ReactiveX;

use Hyperf\Coroutine\Coroutine;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Scheduler;
use Rx\SchedulerInterface;
use Swoole\Event;
use Swoole\Timer;

use function Hyperf\Support\make;

class RxSwoole
{
    private static bool $initialized = false;

    public static function getLoop(): callable
    {
        return function ($ms, $callable) {
            if ($ms === 0) {
                Event::defer(function () use ($callable) {
                    Coroutine::create($callable);
                });
                return new EmptyDisposable();
            }
            $timer = Timer::after($ms, $callable);
            return new CallbackDisposable(function () use ($timer) {
                Timer::clear($timer);
            });
        };
    }

    public static function init()
    {
        if (self::$initialized) {
            return;
        }

        // You only need to set the default scheduler once
        Scheduler::setDefaultFactory(fn () => make(SchedulerInterface::class, ['timerCallableOrLoop' => self::getLoop()]));

        RxSwoole::$initialized = true;
    }
}
