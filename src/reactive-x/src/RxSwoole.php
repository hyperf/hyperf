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

namespace Hyperf\ReactiveX;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Scheduler;
use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Timer;

class RxSwoole
{
    private static $initialized = false;

    public static function init()
    {
        if (self::$initialized) {
            return;
        }
        $loop = function ($ms, $callable) {
            if ($ms === 0) {
                Event::defer(function () use ($callable) {
                    Coroutine::create($callable);
                });
                return new EmptyDisposable();
            }
            $timer = Timer::after($ms, function () use ($callable) {
                Coroutine::create($callable);
            });
            return new CallbackDisposable(function () use ($timer) {
                Timer::clear($timer);
            });
        };

        //You only need to set the default scheduler once
        Scheduler::setDefaultFactory(function () use ($loop) {
            return new Scheduler\EventLoopScheduler($loop);
        });

        RxSwoole::$initialized = true;
    }
}
