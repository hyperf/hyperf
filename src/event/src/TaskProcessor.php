<?php

namespace Hyperf\Event;


use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableTaskInterface;
use Psr\EventDispatcher\TaskInterface;
use Psr\EventDispatcher\TaskProcessorInterface;

class TaskProcessor implements TaskProcessorInterface
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
     * Provide all listeners with a task event to process.
     *
     * @param TaskInterface $event
     *  The task to process.
     * @return TaskInterface
     *  The task that was passed, now modified by callers.
     */
    public function process(TaskInterface $event): TaskInterface
    {
        foreach ($this->listeners->getListenersForEvent($event) as $listener) {
            $listener($event);
            if ($event instanceof StoppableTaskInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }
}