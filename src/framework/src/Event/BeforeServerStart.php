<?php

namespace Hyperf\Framework\Event;


class BeforeServerStart
{

    /**
     * @var string
     */
    private $serverName;

    public function __construct(string $serverName)
    {
        $this->serverName = $serverName;
    }


}