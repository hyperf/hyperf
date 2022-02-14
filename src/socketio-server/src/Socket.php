<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\SocketIOServer;

use Hyperf\SocketIOServer\Emitter\Emitter;
use Hyperf\SocketIOServer\Exception\ConnectionClosedException;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Parser\Engine;
use Hyperf\SocketIOServer\Parser\Packet;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Context;
use Hyperf\WebSocketServer\Sender;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Server;

class Socket
{
    use Emitter;

    public function __construct(
        AdapterInterface $adapter,
        Sender $sender,
        SidProviderInterface $sidProvider,
        private Encoder $encoder,
        int $fd,
        private string $nsp,
        ?callable $addCallback = null
    ) {
        $this->adapter = $adapter;
        $this->sender = $sender;
        $this->addCallback = $addCallback;
        $this->fd = $fd;
        $this->sidProvider = $sidProvider;
    }

    public function getFd(): int
    {
        return $this->fd;
    }

    public function getSid(): string
    {
        return $this->sidProvider->getSid($this->fd);
    }

    public function join(string ...$rooms)
    {
        $this->adapter->add($this->getSid(), ...$rooms);
    }

    public function leave(string ...$rooms)
    {
        $this->adapter->del($this->getSid(), ...$rooms);
    }

    public function leaveAll()
    {
        $this->adapter->del($this->getSid());
    }

    public function disconnect()
    {
        $closePacket = Packet::create([
            'type' => Packet::CLOSE,
            'nsp' => $this->nsp,
        ]);
        //notice client is about to disconnect
        $this->sender->push($this->fd, Engine::MESSAGE . $this->encoder->encode($closePacket));
        /** @var \Swoole\WebSocket\Server $server */
        $server = ApplicationContext::getContainer()->get(Server::class);
        $server->disconnect($this->fd);
    }

    public function getNamespace(): string
    {
        return $this->nsp;
    }

    /**
     * @throws ConnectionClosedException After the WebSocketConnection disconnects, this Exception will be thrown
     */
    public function getRequest(): ServerRequestInterface
    {
        // If the connection is closed (onClose called)，
        // WebSocketContext would have been released.
        // $serverRequest is null in this case.
        $serverRequest = Context::get(ServerRequestInterface::class);
        if (! $serverRequest instanceof ServerRequestInterface) {
            throw new ConnectionClosedException('the request has been freed');
        }
        return $serverRequest;
    }
}
