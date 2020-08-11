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
namespace Hyperf\Amqp;

use Hyperf\Amqp\Connection\AMQPSwooleConnection;
use Hyperf\Amqp\Connection\KeepaliveIO;
use Hyperf\Amqp\Pool\AmqpConnectionPool;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coroutine;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;

class Connection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var AmqpConnectionPool
     */
    protected $pool;

    /**
     * @var null|AbstractConnection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Params
     */
    protected $params;

    /**
     * @var float
     */
    protected $lastHeartbeatTime = 0.0;

    /**
     * @var null|AMQPChannel
     */
    protected $channel;

    /**
     * @var null|AMQPChannel
     */
    protected $confirmChannel;

    public function __construct(ContainerInterface $container, AmqpConnectionPool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = $config;
        $this->context = $container->get(Context::class);
        $this->params = new Params(Arr::get($config, 'params', []));
        $this->connection = $this->initConnection();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection(): AbstractConnection
    {
        if ($this->check()) {
            // The connection is valid, reset the last heartbeat time.
            $currentTime = microtime(true);
            $this->lastHeartbeatTime = $currentTime;

            return $this->connection;
        }

        $this->reconnect();

        return $this->connection;
    }

    public function getChannel(): AMQPChannel
    {
        if (! $this->channel || ! $this->check()) {
            $this->channel = $this->getConnection()->channel();
        }
        return $this->channel;
    }

    public function getConfirmChannel(): AMQPChannel
    {
        if (! $this->confirmChannel || ! $this->check()) {
            $this->confirmChannel = $this->getConnection()->channel();
            $this->confirmChannel->confirm_select();
        }
        return $this->confirmChannel;
    }

    public function reconnect(): bool
    {
        if ($this->connection && $this->connection->getIO() instanceof KeepaliveIO) {
            $this->connection->getIO()->close();
        }

        $this->connection = $this->initConnection();
        $this->channel = null;
        $this->confirmChannel = null;
        return true;
    }

    public function check(): bool
    {
        return isset($this->connection) && $this->connection instanceof AbstractConnection && $this->connection->isConnected() && ! $this->isHeartbeatTimeout();
    }

    public function close(): bool
    {
        $this->connection->close();
        if ($this->connection->getIO() instanceof KeepaliveIO) {
            $this->connection->getIO()->close();
        }

        $this->channel = null;
        $this->confirmChannel = null;
        return true;
    }

    protected function initConnection(): AbstractConnection
    {
        $class = AMQPStreamConnection::class;
        if (Coroutine::id() > 0) {
            $class = AMQPSwooleConnection::class;
        }

        $this->lastHeartbeatTime = microtime(true);
        /** @var AbstractConnection $connection */
        $connection = new $class(
            $this->config['host'] ?? 'localhost',
            $this->config['port'] ?? 5672,
            $this->config['user'] ?? 'guest',
            $this->config['password'] ?? 'guest',
            $this->config['vhost'] ?? '/',
            $this->params->isInsist(),
            $this->params->getLoginMethod(),
            $this->params->getLoginResponse(),
            $this->params->getLocale(),
            $this->params->getConnectionTimeout(),
            $this->params->getReadWriteTimeout(),
            $this->params->getContext(),
            $this->params->isKeepalive(),
            $this->params->getHeartbeat()
        );

        $connection->set_close_on_destruct($this->params->isCloseOnDestruct());

        return $connection;
    }

    protected function isHeartbeatTimeout(): bool
    {
        if ($this->params->getHeartbeat() === 0) {
            return false;
        }

        if (microtime(true) - $this->lastHeartbeatTime > $this->params->getHeartbeat()) {
            return true;
        }

        return false;
    }
}
