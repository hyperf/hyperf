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

namespace Hyperf\Amqp\Pool;

use Hyperf\Amqp\Channel;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Hyperf\Pool\PoolOption;
use Hyperf\Utils\Arr;
use PhpAmqpLib\Connection\AbstractConnection;
use Psr\Container\ContainerInterface;

class AmqpChannelPool extends Pool
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var AmqpConnectionPool
     */
    protected $connectionPool;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('amqp.%s', $this->name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);

        parent::__construct($container);

        $this->connectionPool = $container->get(PoolFactory::class)->getConnectionPool($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function initOption()
    {
        if ($poolOptions = Arr::get($this->config, 'pool')) {
            $option = new PoolOption();
            $option->setMinConnections($poolOptions['min_channels'] ?? 1)
                ->setMaxConnections($poolOptions['max_channels'] ?? 10)
                ->setConnectTimeout(10.0)
                ->setWaitTimeout(3.0)
                ->setHeartbeat(-1);

            $this->option = $option;
        } else {
            parent::initOption();
        }
    }

    protected function createConnection(): ConnectionInterface
    {
        $connection = $this->connectionPool->get();
        /** @var AbstractConnection $amqpConnection */
        $amqpConnection = $connection->getConnection();
        // var_dump('Create a channel.');
        $channel = new Channel($this->container, $this, $amqpConnection->channel());
        $this->connectionPool->release($connection);
        return $channel;
    }

    public function get(): ConnectionInterface
    {
        $channel = parent::get();
        var_dump('Get a channel.');
        return $channel;
    }

    public function release(ConnectionInterface $connection): void
    {
        var_dump('Release a channel.');
        /** @var \PhpAmqpLib\Channel\AMQPChannel $connection */
        parent::release($connection);
    }

}
