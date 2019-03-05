<?php

namespace Hyperf\Framework\Event;


class OnPipeMessage
{

    /**
     * @var \Swoole\Server
     */
    public $server;

    /**
     * @var int
     */
    public $fromWorkerId;

    /**
     * @var string|array|mixed
     */
    public $data;

    public function __construct(\Swoole\Server $server, int $fromWorkerId, $data)
    {
        $this->server = $server;
        $this->fromWorkerId = $fromWorkerId;
        $this->data = $data;
    }


}