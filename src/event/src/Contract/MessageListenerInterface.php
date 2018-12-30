<?php

namespace Hyperf\Event\Contract;


use Psr\EventDispatcher\MessageInterface;

interface MessageListenerInterface
{

    /**
     * @return string[] Returns the events that you want to listen.
     */
    public function listen(): array;

    /**
     * Handler the message event when the event triggered.
     * Notice that this action maybe defered.
     */
    public function process(MessageInterface $event);

}