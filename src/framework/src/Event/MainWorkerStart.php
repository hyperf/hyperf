<?php

namespace Hyperf\Framework\Event;


class MainWorkerStart
{

    /**
     * @var string
     */
    private $serverName;

    /**
     * @var int
     */
    private $workerId;

    public function __construct($serverName, int $workerId)
    {
        $this->serverName = $serverName;
        $this->workerId = $workerId;
    }

}