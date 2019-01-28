<?php

namespace Hyperf\Amqp;


use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Pool;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\Container\ContainerInterface;

class Channel extends BaseConnection implements ConnectionInterface
{

    /**
     * @var AMQPChannel|AbstractChannel
     */
    private $channel;

    public function __construct(ContainerInterface $container, Pool $pool, AMQPChannel $channel)
    {
        parent::__construct($container, $pool);
        $this->channel = $channel;
    }

    public function __call($name, $arguments)
    {
        return $this->channel->{$name}(...$arguments);
    }

    /**
     * Get the real connection from pool.
     */
    public function getConnection()
    {
        return $this->channel;
    }

    /**
     * Reconnect the connection.
     */
    public function reconnect(): bool
    {
        // If the channel is disconnected, then drop it, should not reconnect the original connection.
        return false;
    }

    /**
     * Check the connection is valid.
     */
    public function check(): bool
    {
        return $this->channel->getConnection()->isConnected();
    }

    /**
     * Close the connection.
     */
    public function close(): bool
    {
        $this->channel->close();
        return true;
    }

}