<?php

namespace Hyperf\Framework\Event;


use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;

class BeforeMainServerStart
{

    /**
     * @var object|SwooleServer|SwooleHttpServer
     */
    public $server;

    /**
     * @var array
     */
    public $serverConfig;

    public function __construct($server, array $serverConfig)
    {
        $this->server = $server;
        $this->serverConfig = $serverConfig;
    }


}