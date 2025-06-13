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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Timer;
use Psr\Container\ContainerInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Scheduler;
use Rx\SchedulerInterface;

use function Hyperf\Coroutine\go;
use function Hyperf\Support\make;

class RxSwoole
{
    private static bool $initialized = false;

    private static Timer $timer;

    public static function getLoop(): callable
    {
        return function ($ms, $callable) {
            if ($ms === 0) {
                go($callable);
                return new EmptyDisposable();
            }
            $id = self::$timer->after($ms / 1000, $callable);
            return new CallbackDisposable(static function () use ($id) {
                self::$timer->clear($id);
            });
        };
    }

    public static function init(?ContainerInterface $container = null)
    {
        if (self::$initialized) {
            return;
        }

        self::$timer = new Timer($container?->get(StdoutLoggerInterface::class));

        // You only need to set the default scheduler once
        Scheduler::setDefaultFactory(fn () => make(SchedulerInterface::class, ['timerCallableOrLoop' => self::getLoop()]));

        RxSwoole::$initialized = true;
    }
}
