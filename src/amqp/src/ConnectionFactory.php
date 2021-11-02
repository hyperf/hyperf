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

use Hyperf\Amqp\IO\SwooleIOFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coroutine\Locker;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class ConnectionFactory
{
    protected ConfigInterface $config;

    /**
     * @var AMQPConnection[][]
     */
    protected $connections = [];

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function refresh(string $pool)
    {
        $config = $this->getConfig($pool);
        $count = $config['pool']['connections'] ?? 1;

        if (Locker::lock(static::class)) {
            try {
                for ($i = 0; $i < $count; ++$i) {
                    $connection = $this->make($config);
                    $this->connections[$pool][] = $connection;
                }
            } finally {
                Locker::unlock(static::class);
            }
        }
    }

    public function getConnection(string $pool): AMQPConnection
    {
        if (! empty($this->connections[$pool])) {
            $index = array_rand($this->connections[$pool]);
            $connection = $this->connections[$pool][$index];
            if (! $connection->isConnected()) {
                if (Locker::lock(static::class . 'getConnection')) {
                    try {
                        unset($this->connections[$pool][$index]);
                        $connection->close();
                        $connection = $this->make($this->getConfig($pool));
                        $this->connections[$pool][] = $connection;
                    } finally {
                        Locker::unlock(static::class . 'getConnection');
                    }
                } else {
                    return $this->getConnection($pool);
                }
            }

            return $connection;
        }

        $this->refresh($pool);
        return Arr::random($this->connections[$pool]);
    }

    public function make(array $config): AMQPConnection
    {
        $user = $config['user'] ?? 'guest';
        $password = $config['password'] ?? 'guest';
        $vhost = $config['vhost'] ?? '/';
        $ioFactory = $config['io'] ?? SwooleIOFactory::class;
        if (is_string($ioFactory)) {
            $ioFactory = new $ioFactory();
        }

        $params = new Params(Arr::get($config, 'params', []));
        $io = $ioFactory($config, $params);

        $connection = new AMQPConnection(
            $user,
            $password,
            $vhost,
            $params->isInsist(),
            $params->getLoginMethod(),
            null,
            $params->getLocale(),
            $io,
            $params->getHeartbeat(),
            $params->getConnectionTimeout(),
            $params->getChannelRpcTimeout()
        );

        return $connection->setParams($params)
            ->setLogger($this->container->get(StdoutLoggerInterface::class));
    }

    protected function getConfig(string $pool): array
    {
        $key = sprintf('amqp.%s', $pool);
        if (! $this->config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        return $this->config->get($key);
    }
}
