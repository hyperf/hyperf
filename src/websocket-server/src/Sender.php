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
use Hyperf\WebSocketServer\Exception\MethodInvalidException;
use Psr\Container\ContainerInterface;

/**
 * @method push(int $fd, $data, int $opcode = null, $finish = null)
 */
class Sender
{
    protected $container;

    protected $logger;

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

    public function proxy($name, $arguments): bool
    {
        $fd = $this->getFdFromProxyMethod($name, $arguments);
        if ($fd === false) {
            throw new MethodInvalidException($arguments);
        }

        $ret = $this->check($fd);
        if ($ret) {
            $this->getServer()->push(...$arguments);
            $this->logger->debug("[WebSocket] Worker.{$this->workerId} send to #{$fd}");
        }

        return $ret;
    }

    /**
     * @param int $workerId
     */
    public function setWorkerId(int $workerId): void
    {
        $this->workerId = $workerId;
    }

    public function check($fd): bool
    {
        $server = $this->getServer();
        $info = $server->connection_info($fd);

        if ($info && $info['websocket_status'] == WEBSOCKET_STATUS_ACTIVE) {
            return true;
        }

        return false;
    }

    /**
     * @param $method
     * @param mixed $arguments
     * @return null|bool|int
     */
    protected function getFdFromProxyMethod($method, $arguments)
    {
        if (in_array($method, ['push', 'send', 'sendto'])) {
            return $arguments[0];
        }

        return false;
    }

    protected function getServer()
    {
        return $this->container->get(ServerFactory::class)->getServer()->getServer();
    }

    protected function sendPipeMessage($name, $arguments)
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
