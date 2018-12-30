<?php

namespace Hyperf\Event;


use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\MessageInterface;
use Psr\EventDispatcher\MessageNotifierInterface;
use Psr\EventDispatcher\TaskProcessorInterface;

class EventManager
{

    /**
     * @var MessageNotifierInterface
     */
    private $notifer;

    /**
     * @var TaskProcessorInterface
     */
    private $processor;

    public function __construct(
        MessageNotifierInterface $notifer,
        TaskProcessorInterface $processor
    ) {
        $this->notifer = $notifer;
        $this->processor = $processor;
    }

    public function trigger($event)
    {
        return $this->processor->process($event);
    }

    public function notify(MessageInterface $event)
    {
        $this->notifer->notify($event);
    }

}