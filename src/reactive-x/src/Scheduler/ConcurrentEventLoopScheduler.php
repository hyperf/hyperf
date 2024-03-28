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

namespace Hyperf\ReactiveX\Scheduler;

use Hyperf\Coroutine\Coroutine;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Scheduler\VirtualTimeScheduler;

/**
 * Class ConcurrentEventLoopScheduler is almost same as EventLoopScheduler,
 * except all calls are invoked in a separate coroutine.
 */
final class ConcurrentEventLoopScheduler extends VirtualTimeScheduler
{
    private int $nextTimer = PHP_INT_MAX;

    private bool $insideInvoke = false;

    /**
     * @var callable
     */
    private mixed $delayCallback;

    private DisposableInterface $currentTimer;

    /**
     * EventLoopScheduler constructor.
     */
    public function __construct(callable $timerCallableOrLoop)
    {
        $this->delayCallback = $timerCallableOrLoop;
        $this->currentTimer = new EmptyDisposable();
        parent::__construct($this->now(), fn ($a, $b) => $a - $b);
    }

    public function scheduleAbsoluteWithState($state, int $dueTime, callable $action): DisposableInterface
    {
        $disp = new CompositeDisposable([
            parent::scheduleAbsoluteWithState($state, $dueTime, $action),
            new CallbackDisposable(function () use ($dueTime) {
                if ($dueTime > $this->nextTimer) {
                    return;
                }
                $this->scheduleStartup();
            }),
        ]);
        if ($this->insideInvoke) {
            return $disp;
        }
        if ($this->nextTimer <= $dueTime) {
            return $disp;
        }
        $this->scheduleStartup();
        return $disp;
    }

    public function start()
    {
        $this->clock = $this->now();
        $this->insideInvoke = true;
        $this->nextTimer = PHP_INT_MAX;
        while ($this->queue->count() > 0) {
            $next = $this->getNext();
            if ($next !== null) {
                if ($next->getDueTime() > $this->clock) {
                    $this->nextTimer = $next->getDueTime();
                    $this->currentTimer = call_user_func($this->delayCallback, $this->nextTimer - $this->clock, [$this, 'start']);
                    break;
                }
                Coroutine::create(function () use ($next) {
                    $next->inVoke();
                });
            }
        }
        $this->insideInvoke = false;
    }

    public function now(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    private function scheduleStartup()
    {
        if ($this->insideInvoke) {
            return;
        }
        $this->currentTimer->dispose();
        $this->nextTimer = $this->getClock();
        $this->currentTimer = call_user_func($this->delayCallback, 0, [$this, 'start']);
    }
}
