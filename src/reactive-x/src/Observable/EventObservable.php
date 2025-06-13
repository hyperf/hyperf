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

namespace Hyperf\ReactiveX\Observable;

use Hyperf\Context\ApplicationContext;
use Psr\EventDispatcher\ListenerProviderInterface;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class EventObservable extends Observable
{
    public function __construct(private string $eventName, private ?SchedulerInterface $scheduler = null)
    {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $provider = ApplicationContext::getContainer()->get(ListenerProviderInterface::class);
        $provider->on($this->eventName, function ($event) use ($observer) {
            if ($this->scheduler === null) {
                $this->scheduler = Scheduler::getDefault();
            }
            return $this->scheduler->schedule(function () use ($observer, $event) {
                $observer->onNext($event);
            });
        });
        return new EmptyDisposable();
    }
}
