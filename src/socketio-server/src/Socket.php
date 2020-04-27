<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\SocketIOServer;

use Hyperf\SocketIOServer\Emitter\Emitter;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Parser\Packet;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Sender;
use Swoole\Server;

class Socket
{
    use Emitter;

    /**
     * @var string
     */
    private $nsp;

    /**
     * @var Encoder
     */
    private $encoder;

    public function __construct(
        AdapterInterface $adapter,
        Sender $sender,
        SidProviderInterface $sidProvider,
        Encoder $encoder,
        int $fd,
        string $nsp,
        ?callable $addCallback = null
    ) {
        $this->adapter = $adapter;
        $this->sender = $sender;
        $this->addCallback = $addCallback;
        $this->fd = $fd;
        $this->nsp = $nsp;
        $this->sidProvider = $sidProvider;
        $this->encoder = $encoder;
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
        $this->sender->push($this->fd, '4' . $this->encoder->encode($closePacket));
        /** @var \Swoole\WebSocket\Server $server */
        $server = ApplicationContext::getContainer()->get(Server::class);
        $server->disconnect($this->fd);
    }

    public function getNsp()
    {
        return $this->nsp;
    }
}
