<?php


namespace Hyperf\WebSocketServer\SocketIO;


use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Coroutine\Channel;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Timer;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class SocketIOController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @var WebSocketServer
     */
    protected $server;

    /**
     * @var []callable
     */
    protected $events = [];

    /**
     * @var []Channel
     */
    protected $clientCallbacks = [];

    protected $clientCallbackTimeout = 10000;

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        if ($index = strpos($frame->data, '[')) {
            $code = substr($frame->data, 0, $index);
            $data = json_decode(substr($frame->data, $index), true);
        } else {
            $code = $frame->data;
            $data = [];
        }

        switch (mb_strlen($code)) {
            case 0:
                break;
            case 1:
                switch ($code) {
                    case '2':   //client ping
                        $server->push($frame->fd, '3'); //sever pong
                        break;
                }
                break;
            case 2:
                switch ($code) {
                    case '41':   //client disconnect
                        $server->disconnect($fd);
                        break;
                    case '42':   //client message
                        $this->dispatch(...$data);
                        break;
                }
                break;
            default:
                switch ($code[0]) {
                    case '4':   //client message
                        switch ($code[1]) {
                            case '2':   //client message with ack
                                $ackId = substr($code, 2);
                                $this->dispatch($frame->fd, $data[0], $data[1],  function($data) use ($frame, $ackId){
                                    $this->server->push($frame->fd, '43'.$ackId.json_encode($data));
                                });
                                break;
                            case '3':   //client reply to message with ack
                                $ackId = substr($code, 2);
                                if ($this->clientCallbacks[$ackId] instanceof Channel){
                                    $this->clientCallbacks[$ackId]->push($data);
                                    unset($this->clientCallbacks[$ackId]);
                                }
                                break;
                        }
                        break;
                }
                break;
        }
    }

    private function dispatch(int $fd, string $event, $payload = null, ?callable $ack = null)
    {
        $socket = make(Socket::class, ['server' => $this->server, 'fd' => $fd, 'controller' => $this]);
        $handler = EventAnnotationCollector::getEventHandler(static::class, $event);
        if ($handler) {
            $result = $this->{$handler}($socket, $payload);
        }
        if (isset($this->events[$event])) {
            $result = $this->events[$event]($socket, $payload);
        }
        $ack && $ack($result ?? null);
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        $this->server = $server;
        static $i;
        $i = $i ?? 0;
        $data = [
            'sid' => (string)($i++),
            'upgrades' => ['websocket'],
            'pingInterval' => 25000,
            'pingTimeout' => 60000,
        ];
        $server->push($request->fd, '0' . json_encode($data)); //socket is open
        $server->push($request->fd, '40');  //client is connected
        $this->dispatch($request->fd, 'connection', $request);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $this->dispatch($fd, 'disconnect');
    }

    /**
     * register socketio event
     * @param $event
     * @param callable $callback
     */
    public function on($event, callable $callback)
    {
        if (is_string($event)) {
            $this->events[$event] = $callback;
        }
    }

    public function addClientCallback(int $ackId, Channel $channel)
    {
        $this->clientCallbacks[$ackId] = $channel;
        // Clean up using timer to avoid memory leak.
        Timer::after($this->clientCallbackTimeout, function() use ($ackId){
            unset($this->clientCallbacks[$ackId]);
        });
    }
}
