<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Event;

use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\MessageInterface;
use Psr\EventDispatcher\MessageNotifierInterface;
use Throwable;

class MessageNotifier implements MessageNotifierInterface
{
    /**
     * @var ListenerProviderInterface
     */
    private $listeners;

    public function __construct(ListenerProviderInterface $listeners)
    {
        $this->listeners = $listeners;
    }

    /**
     * Notify listeners of a message event.
     * This method MAY act asynchronously.  Callers SHOULD NOT
     * assume that any action has been taken when this method
     * returns.
     *
     * @param MessageInterface $event
     *   The event to notify listeners of.
     */
    public function notify(MessageInterface $event): void
    {
        $position   = 0;
        $exceptions = [];
        foreach ($this->listeners->getListenersForEvent($event) as $listener) {
            try {
                $listener($event);
            } catch (Throwable $e) {
                $exceptions[$position] = $e;
            }
            $position += 1;
        }
        if ([] !== $exceptions) {
            throw Exception\ExceptionAggregate::fromExceptions($exceptions);
        }
    }
}
