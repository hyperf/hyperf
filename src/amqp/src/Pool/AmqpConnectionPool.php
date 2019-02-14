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

use Hyperf\Amqp\Connection;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Hyperf\Pool\PoolOption;
use Hyperf\Utils\Arr;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class AmqpConnectionPool extends Pool
{
    protected $name;

    protected $config;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('amqp.%s', $this->name);
        if (! $config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);

        parent::__construct($container);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function release(ConnectionInterface $connection): void
    {
        parent::release($connection);
    }

    protected function initOption(): void
    {
        if ($poolOptions = Arr::get($this->config, 'pool')) {
            $option = new PoolOption();
            $option->setMinConnections($poolOptions['min_connections'] ?? 1);
            $option->setMaxConnections($poolOptions['max_connections'] ?? 10);
            $option->setConnectTimeout($poolOptions['connect_timeout'] ?? 10.0);
            $option->setWaitTimeout($poolOptions['wait_timeout'] ?? 3.0);
            $option->setHeartbeat($poolOptions['heartbeat'] ?? -1);

            $this->option = $option;
        } else {
            parent::initOption();
        }
    }

    protected function createConnection(): ConnectionInterface
    {
        return new Connection($this->container, $this, $this->config);
    }
}
