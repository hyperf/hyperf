<?php

namespace Hyperf\Framework\Event;


class BeforeServerStart
{

    /**
     * @var string
     */
    public $serverName;

    public function __construct(string $serverName)
    {
        $this->serverName = $serverName;
    }


}