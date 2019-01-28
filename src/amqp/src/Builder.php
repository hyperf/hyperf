<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp;

use Hyperf\Amqp\Message\MessageInterface;
use Hyperf\Amqp\Pool\AmqpChannelPool;
use Hyperf\Amqp\Pool\AmqpConnectionPool;
use Hyperf\Amqp\Pool\PoolFactory;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Psr\Container\ContainerInterface;

class Builder
{
    protected $name = 'default';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var PoolFactory
     */
    private $poolFactory;

    public function __construct(ContainerInterface $container, PoolFactory $poolFactory)
    {
        $this->container = $container;
        $this->poolFactory = $poolFactory;
    }

    public function declare(MessageInterface $message, ?AMQPChannel $channel = null): void
    {
        if (! $channel) {
            [$channel, $connection] = $this->getChannel($message->getPoolName());
        }

        $builder = $message->getExchangeBuilder();

        $channel->exchange_declare($builder->getExchange(), $builder->getType(), $builder->isPassive(), $builder->isDurable(), $builder->isAutoDelete(), $builder->isInternal(), $builder->isNowait(), $builder->getArguments(), $builder->getTicket());

        isset($connection) && $this->getConnectionPool($message->getPoolName())->release($connection);
    }

    protected function getChannel(string $poolName): AMQPChannel
    {
        $pool = $this->getChannelPool($poolName);
        return $pool->get();
    }

    protected function getConnection(string $poolName): Connection
    {
        return $this->poolFactory->getConnectionPool($poolName)->get();
    }

    protected function getConnectionPool(string $poolName): AmqpConnectionPool
    {
        return $this->poolFactory->getConnectionPool($poolName);
    }

    protected function getChannelPool(string $poolName): AmqpChannelPool
    {
        return $this->poolFactory->getChannelPool($poolName);
    }

}
