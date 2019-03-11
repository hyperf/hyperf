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

use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use Hyperf\Amqp\Connection;
use InvalidArgumentException;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\ConnectionInterface;

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
        $options = Arr::get($this->config, 'pool', []);

        parent::__construct($container, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function release(ConnectionInterface $connection): void
    {
        parent::release($connection);
    }

    protected function createConnection(): ConnectionInterface
    {
        return new Connection($this->container, $this, $this->config);
    }

    protected function getConnectionId(): string
    {
        return static::class . '.' . $this->getName();
    }
}
