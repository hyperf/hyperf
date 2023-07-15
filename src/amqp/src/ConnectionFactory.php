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

use Hyperf\Amqp\Exception\NotSupportedException;
use Hyperf\Amqp\IO\IOFactory;
use Hyperf\Amqp\IO\IOFactoryInterface;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Locker;
use InvalidArgumentException;
use PhpAmqpLib\Wire\IO\AbstractIO;
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

        $key = $this->lockKey($pool, 'refresh');
        if (Locker::lock($key)) {
            try {
                for ($i = 0; $i < $count; ++$i) {
                    $connection = $this->make($config);
                    $this->connections[$pool][] = $connection;
                }
            } finally {
                Locker::unlock($key);
            }
        }
    }

    public function getConnection(string $pool): AMQPConnection
    {
        if (! empty($this->connections[$pool])) {
            $index = array_rand($this->connections[$pool]);
            $connection = $this->connections[$pool][$index];
            if (! $connection->isConnected()) {
                $key = $this->lockKey($pool, 'connection');
                if (Locker::lock($key)) {
                    try {
                        unset($this->connections[$pool][$index]);
                        $connection->close();
                        $connection = $this->make($this->getConfig($pool));
                        $this->connections[$pool][] = $connection;
                    } finally {
                        Locker::unlock($key);
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
        $params = new Params(Arr::get($config, 'params', []));
        $io = $this->makeIO($config, $params);

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

    private function makeIO(array $config, Params $params): AbstractIO
    {
        $callable = $config['io'] ?? IOFactory::class;

        if (is_callable($callable)) {
            return $callable($config, $params);
        }

        $ioFactory = $this->container->get((string) $callable);
        if (! $ioFactory instanceof IOFactoryInterface) {
            throw new NotSupportedException(sprintf('%s must instanceof %s', $callable, IOFactoryInterface::class));
        }

        return $ioFactory->create($config, $params);
    }

    private function lockKey(string $pool, string $position): string
    {
        return sprintf('%s:%s:%s', static::class, $pool, $position);
    }
}
