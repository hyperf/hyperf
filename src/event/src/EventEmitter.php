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

use Psr\EventDispatcher\EventInterface;
use Psr\EventDispatcher\MessageInterface;
use Psr\EventDispatcher\MessageNotifierInterface;
use Psr\EventDispatcher\TaskInterface;
use Psr\EventDispatcher\TaskProcessorInterface;

class EventEmitter
{
    /**
     * @var MessageNotifierInterface
     */
    private $notifier;

    /**
     * @var TaskProcessorInterface
     */
    private $processor;

    public function __construct(
        MessageNotifierInterface $notifer,
        TaskProcessorInterface $processor
    ) {
        $this->notifier = $notifer;
        $this->processor = $processor;
    }

    /**
     * Emit a event,
     * if the event is a task, then will provide all listeners with the task to process,
     * if the event is a message, the will notify all listners, this action MAY act asynchronously.
     */
    public function emit(EventInterface $event): void
    {
        if ($event instanceof MessageInterface) {
            $this->notify($event);
        } else {
            $this->process($event);
        }
    }

    /**
     * Provide all listeners with a task event to process.
     */
    public function process(TaskInterface $event): TaskInterface
    {
        return $this->processor->process($event);
    }

    /**
     * Notify listeners of a message event.
     *
     * This method MAY act asynchronously.
     * Callers SHOULD NOT assume that any action has been
     * taken when this method returns.
     */
    public function notify(MessageInterface $event): void
    {
        $this->notifier->notify($event);
    }
}
