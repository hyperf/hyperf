<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\WebSocketServer;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\WebSocketServer\Exception\InvalidMethodException;
use Psr\Container\ContainerInterface;
use Swoole\Server;

/**
 * @method push(int $fd, $data, int $opcode = null, $finish = null)
 */
class Sender
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $workerId;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function __call($name, $arguments)
    {
        if (! $this->proxy($name, $arguments)) {
            $this->sendPipeMessage($name, $arguments);
        }
    }

    public function proxy(string $name, array $arguments): bool
    {
        $fd = $this->getFdFromProxyMethod($name, $arguments);

        $result = $this->check($fd);
        if ($result) {
            $this->getServer()->push(...$arguments);
            $this->logger->debug("[WebSocket] Worker.{$this->workerId} send to #{$fd}");
        }

        return $result;
    }

    public function setWorkerId(int $workerId): void
    {
        $this->workerId = $workerId;
    }

    public function check($fd): bool
    {
        $info = $this->getServer()->connection_info($fd);

        if ($info && $info['websocket_status'] === WEBSOCKET_STATUS_ACTIVE) {
            return true;
        }

        return false;
    }

    protected function getFdFromProxyMethod(string $method, array $arguments): int
    {
        if (! in_array($method, ['push', 'send', 'sendto'])) {
            throw new InvalidMethodException(sprintf('Method [%s] is not allowed.', $method));
        }

        return (int) $arguments[0];
    }

    protected function getServer(): Server
    {
        return $this->container->get(Server::class);
    }

    protected function sendPipeMessage(string $name, array $arguments): void
    {
        $server = $this->getServer();
        $workerCount = $server->setting['worker_num'] - 1;
        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId !== $this->workerId) {
                $server->sendMessage(new SenderPipeMessage($name, $arguments), $workerId);
                $this->logger->debug("[WebSocket] Let Worker.{$workerId} try to {$name}.");
            }
        }
    }
}
