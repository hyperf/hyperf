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
namespace Hyperf\Amqp\Connection;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Wire\AMQPWriter;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Client;
use Swoole\Timer;

class Socket
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var null|int
     */
    protected $timerId;

    /**
     * @var bool
     */
    protected $connected = false;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @var int
     */
    protected $heartbeat;

    /**
     * @var float
     */
    protected $waitTimeout = 10.0;

    public function __construct(string $host, int $port, float $timeout, int $heartbeat)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->heartbeat = $heartbeat;

        $this->connect();
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function call(\Closure $closure)
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        $client = $this->channel->pop($this->waitTimeout);
        if ($client === false) {
            throw new AMQPRuntimeException('Socket of keepaliveIO is exhausted. Cannot establish new socket before wait_timeout.');
        }

        try {
            $result = $closure($client);
        } finally {
            $this->channel->push($client);
        }

        return $result;
    }

    public function connect()
    {
        $sock = new Client(SWOOLE_SOCK_TCP);
        if (! $sock->connect($this->host, $this->port, $this->timeout)) {
            throw new AMQPRuntimeException(
                sprintf(
                    'Error Connecting to server(%s): %s ',
                    $sock->errCode,
                    swoole_strerror($sock->errCode)
                ),
                $sock->errCode
            );
        }

        $this->channel = new Channel(1);
        $this->channel->push($sock);
        $this->connected = true;

        $this->addHeartbeat();
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function close()
    {
        $this->connected = false;
        $this->clear();
    }

    /**
     * Sends a heartbeat message.
     */
    public function heartbeat()
    {
        $pkt = new AMQPWriter();
        $pkt->write_octet(8);
        $pkt->write_short(0);
        $pkt->write_long(0);
        $pkt->write_octet(0xCE);
        $data = $pkt->getvalue();

        $this->call(function ($client) use ($data) {
            $buffer = $client->send($data);
            if ($buffer === false) {
                throw new AMQPRuntimeException('Error sending data');
            }
        });
    }

    protected function addHeartbeat()
    {
        $this->clear();
        $this->timerId = Timer::tick($this->heartbeat * 1000, function () {
            try {
                if ($this->isConnected()) {
                    $this->heartbeat();
                }
            } catch (\Throwable $throwable) {
                $this->close();
                if ($logger = $this->getLogger()) {
                    $message = sprintf('KeepaliveIO heartbeat failed, %s', (string) $throwable);
                    $logger->error($message);
                }
            }
        });
    }

    protected function clear()
    {
        if ($this->timerId) {
            Timer::clear($this->timerId);
            $this->timerId = null;
        }
    }

    protected function getLogger(): ?LoggerInterface
    {
        if (ApplicationContext::hasContainer() && $container = ApplicationContext::getContainer()) {
            if ($container->has(StdoutLoggerInterface::class)) {
                return $container->get(StdoutLoggerInterface::class);
            }
        }

        return null;
    }
}
