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

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LoggerInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ListenerProviderInterface $listeners,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Provide all listeners with an event to process.
     *
     * @param object $event The object to process
     * @return object The Event that was passed, now modified by listeners
     */
    public function dispatch(object $event)
    {
        foreach ($this->listeners->getListenersForEvent($event) as $listener) {
            $listener($event);
            $this->dump($listener, $event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

    /**
     * Dump the debug message if $logger property is provided.
     * @param mixed $listener
     */
    private function dump($listener, object $event)
    {
        if (! $this->logger) {
            return;
        }
        $eventName = get_class($event);
        $listenerName = '[ERROR TYPE]';
        if (is_array($listener)) {
            $listenerName = is_string($listener[0]) ? $listener[0] : get_class($listener[0]);
        } elseif (is_string($listener)) {
            $listenerName = $listener;
        } elseif (is_object($listener)) {
            $listenerName = get_class($listener);
        }
        $this->logger->debug(sprintf('Event %s handled by %s listener.', $eventName, $listenerName));
    }
}
