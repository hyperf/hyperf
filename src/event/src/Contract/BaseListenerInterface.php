<?php


namespace Hyperf\Event\Contract;


interface BaseListenerInterface
{

    /**
     * @return string[] Returns the events that you want to listen.
     */
    public function listen(): array;

}