<?php

namespace Hyperf\Event;


use Psr\EventDispatcher\EventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use SplPriorityQueue;

class ListenerProvider implements ListenerProviderInterface
{

    /**
     * @var callable[]
     */
    public $listeners = [];

    /**
     * @param EventInterface $event
     *   An event for which to return the relevant listeners.
     * @return iterable[callable]
     *   An iterable (array, iterator, or generator) of callables.  Each
     *   callable MUST be type-compatible with $event.
     */
    public function getListenersForEvent(EventInterface $event): iterable
    {
        $queue = new SplPriorityQueue();
        foreach ($this->listeners as $listener) {
            if ($event instanceof $listener->event) {
                $queue->insert($listener->listener, $listener->priority);
            }
        }
        return $queue;
    }

    public function on(string $event, callable $listener, int $priority = 1) : void
    {
        $this->listeners[] = new ListenerData($event, $listener, $priority);
    }

}