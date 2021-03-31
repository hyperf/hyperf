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

use Hyperf\SocketIOServer\Collector\SocketIORouter;
use Hyperf\SocketIOServer\Emitter\Emitter;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Parser\Engine;
use Hyperf\SocketIOServer\Parser\Packet;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\Room\EphemeralInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Sender;

class BaseNamespace implements NamespaceInterface
{
    use Emitter;

    /**
     * @var array<string, callable[]>
     */
    private $eventHandlers = [];

    public function __construct(Sender $sender, SidProviderInterface $sidProvider)
    {
        /* @var AdapterInterface adapter */
        $this->adapter = make(AdapterInterface::class, ['sender' => $sender, 'nsp' => $this]);
        if ($this->adapter instanceof EphemeralInterface) {
            $this->adapter = $this->adapter->setTtl(
                SocketIOConfig::getPingInterval() + SocketIOConfig::getPingTimeout()
            );
        }
        $this->sidProvider = $sidProvider;
        $this->sender = $sender;
        $this->broadcast = true;
        $this->on('connect', function (Socket $socket) {
            $this->adapter->add($socket->getSid(), $socket->getSid());
        });
        $this->on('disconnect', function (Socket $socket) {
            $this->adapter->del($socket->getSid());
        });
    }

    /**
     * Register socket.io event.
     */
    public function on(string $event, callable $callback)
    {
        $this->eventHandlers[$event][] = $callback;
    }

    /**
     * Retrieves all callbacks for any events.
     * @return array<string, callable[]>
     */
    public function getEventHandlers()
    {
        return $this->eventHandlers;
    }

    /**
     * Returns the current namespace in string form.
     */
    public function getNamespace(): string
    {
        return (string) SocketIORouter::getNamespace(static::class);
    }

    /**
     * Kick off a client from room, possibly remotely.
     */
    public function dismiss(string $roomId)
    {
        $closePacket = Packet::create([
            'type' => Packet::CLOSE,
            'nsp' => $this->getNamespace(),
        ]);
        $encoder = ApplicationContext::getContainer()->get(Encoder::class);
        $this->adapter->broadcast(
            Engine::MESSAGE . $encoder->encode($closePacket),
            ['room' => $roomId, 'flag' => ['close' => true]]
        );
    }
}
