<?php

namespace Hyperf\Event\Contract;


use Psr\EventDispatcher\EventInterface;

interface TaskListenerInterface
{

    /**
     * @return string[] Returns the events that you want to listen.
     */
    public function listen(): array;

    /**
     * Handler the task event when the event triggered.
     */
    public function process(EventInterface $event): EventInterface;

}