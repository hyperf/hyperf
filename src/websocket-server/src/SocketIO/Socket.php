<?php


namespace Hyperf\WebSocketServer\SocketIO;

use Swoole\Coroutine\Channel;
use Swoole\WebSocket\Server as WebSocketServer;

class Socket
{
    /**
     * @var WebSocketServer
     */
    private $server;
    /**
     * @var int
     */
    private $id;
    /**
     * @var SocketIOController
     */
    private $controller;

    public function __construct(WebSocketServer $server, int $fd, SocketIOController $controller)
    {
        $this->server = $server;
        $this->controller = $controller;
        $this->id = $fd;
    }

    public function emit(string $event, $data, ?Channel $channel = null)
    {
        if (!$channel) {
            return $this->server->push($this->id, '42'.json_encode([$event, $data]));
        }
        static $i;
        $i = $i ?? 0;
        $this->controller->addClientCallback($i, $channel);
        return $this->server->push($this->id, '42'.$i++.json_encode([$event, $data]));
    }

    public function disconnect()
    {
        $this->server->push($this->id, '41'); //notice client is about to disconnect
        $this->server->disconnect($this->id);
    }
}
