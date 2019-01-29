<?php

namespace Hyperf\Amqp;


use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Pool;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\Container\ContainerInterface;

/**
 * @method mixed flow($active)
 * @method mixed access_request($realm, $exclusive = false, $passive = false, $active = false, $write = false, $read = false)
 * @method mixed|null exchange_declare($exchange, $type, $passive = false, $durable = false, $auto_delete = true, $internal = false, $nowait = false, $arguments = array(), $ticket = null)
 * @method mixed|null exchange_delete($exchange, $if_unused = false, $nowait = false, $ticket = null)
 * @method mixed|null exchange_bind($destination, $source, $routing_key = '', $nowait = false, $arguments = array(), $ticket = null)
 * @method mixed exchange_unbind($destination, $source, $routing_key = '', $nowait = false, $arguments = array(), $ticket = null)
 * @method mixed|null queue_bind($queue, $exchange, $routing_key = '', $nowait = false, $arguments = array(), $ticket = null)
 * @method mixed queue_unbind($queue, $exchange, $routing_key = '', $arguments = array(), $ticket = null)
 * @method mixed|null queue_declare($queue = '', $passive = false, $durable = false, $exclusive = false, $auto_delete = true, $nowait = false, $arguments = array(), $ticket = null)
 * @method mixed|null queue_delete($queue = '', $if_unused = false, $if_empty = false, $nowait = false, $ticket = null)
 * @method mixed|null queue_purge($queue = '', $nowait = false, $ticket = null)
 * @method void basic_ack($delivery_tag, $multiple = false)
 * @method mixed basic_cancel($consumer_tag, $nowait = false, $noreturn = false)
 * @method mixed|string basic_consume($queue = '', $consumer_tag = '', $no_local = false, $no_ack = false, $exclusive = false, $nowait = false, $callback = null, $ticket = null, $arguments = array())
 * @method mixed basic_get($queue = '', $no_ack = false, $ticket = null)
 * @method void basic_publish($msg, $exchange = '', $routing_key = '', $mandatory = false, $immediate = false, $ticket = null)
 * @method void batch_basic_publish($msg, $exchange = '', $routing_key = '', $mandatory = false, $immediate = false, $ticket = null)
 * @method void publish_batch()
 * @method mixed basic_qos($prefetch_size, $prefetch_count, $a_global)
 * @method mixed basic_recover($requeue = false)
 * @method void basic_reject($delivery_tag, $requeue)
 * @method mixed tx_commit()
 * @method mixed tx_rollback()
 * @method mixed tx_select()
 * @method null confirm_select($nowait = false)
 * @method void wait_for_pending_acks($timeout = 0)
 * @method void wait_for_pending_acks_returns($timeout = 0)
 * @method void set_return_listener($callback)
 * @method void set_nack_handler($callback)
 * @method void set_ack_handler($callback)
 */
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