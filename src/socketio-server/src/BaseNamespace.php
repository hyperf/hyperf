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

use Hyperf\SocketIOServer\Collector\EventAnnotationCollector;
use Hyperf\SocketIOServer\Emitter\Emitter;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\WebSocketServer\Sender;

class BaseNamespace
{
    use Emitter;

    /**
     * @var array<string, callable[]>
     */
    private $eventHandlers;

    public function __construct(Sender $sender, SidProviderInterface $sidProvider)
    {
        /* @var AdapterInterface adapter */
        $this->adapter = make(AdapterInterface::class, ['sender' => $sender, 'nsp' => $this]);
        $this->sidProvider = $sidProvider;
        $this->sender = $sender;
        $this->eventHandlers = ['/' => []];
        $this->broadcast = true;
        $this->on('connect', function (Socket $socket) {
            $this->adapter->add($socket->getSid(), $socket->getSid());
        });
        $this->on('disconnect', function (Socket $socket) {
            $this->adapter->del($socket->getSid());
        });
    }

    /**
     * register socketio event.
     */
    public function on(string $event, callable $callback)
    {
        EventAnnotationCollector::collectInlineEvent((string) $this->getNsp(), $event, $callback);
    }

    /**
     * Get the adatper for namespace.
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
