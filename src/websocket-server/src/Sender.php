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
use Hyperf\Server\ServerFactory;
use Hyperf\WebSocketServer\Exception\InvalidMethodException;
use Psr\Container\ContainerInterface;

/**
 * @method push(int $fd, $data, int $opcode = null, $finish = null)
 */
class Sender
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var \Hyperf\Contract\StdoutLoggerInterface
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
        if ($fd === false) {
            throw new InvalidMethodException($arguments);
        }

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

    /**
     * @return null|bool|int
     */
    protected function getFdFromProxyMethod(string $method, array $arguments)
    {
        if (in_array($method, ['push', 'send', 'sendto'])) {
            return $arguments[0];
        }

        return false;
    }

    protected function getServer(): \Swoole\Server
    {
        return $this->container->get(ServerFactory::class)->getServer()->getServer();
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
