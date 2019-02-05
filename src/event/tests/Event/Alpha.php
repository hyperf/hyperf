<?php

namespace HyperfTest\Event\Event;


use Hyperf\Event\Stoppable;
use Psr\EventDispatcher\StoppableEventInterface;

class Alpha implements StoppableEventInterface
{

    use Stoppable;

}