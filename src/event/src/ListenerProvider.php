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

namespace Hyperf\Event;

use Hyperf\Stdlib\SplPriorityQueue;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var ListenerData[]
     */
    public array $listeners = [];

    /**
     * @var array<class-string, iterable<callable>>
     */
    protected array $listenersCache = [];

    /**
     * @param object $event An event for which to return the relevant listeners
     * @return iterable<callable> An iterable (array, iterator, or generator) of callables.  Each
     *                            callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent($event): iterable
    {
        $eventClass = $event::class;
        $isAnonymousClass = str_contains($eventClass, '@anonymous');

        if (! $isAnonymousClass && isset($this->listenersCache[$eventClass])) {
            return clone $this->listenersCache[$eventClass];
        }

        $queue = new SplPriorityQueue();

        foreach ($this->listeners as $listener) {
            if ($event instanceof $listener->event) {
                $queue->insert($listener->listener, $listener->priority);
            }
        }

        if (! $isAnonymousClass) {
            $this->listenersCache[$eventClass] = clone $queue;
        }

        return $queue;
    }

    public function on(string $event, callable $listener, int $priority = ListenerData::DEFAULT_PRIORITY): void
    {
        $this->listeners[] = new ListenerData($event, $listener, $priority);
    }
}
