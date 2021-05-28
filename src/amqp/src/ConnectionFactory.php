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

use Hyperf\Amqp\IO\SwooleIO;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Arr;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class ConnectionFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var AMQPConnection[][]
     */
    protected $connections = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function refresh(string $pool)
    {
        $key = sprintf('amqp.%s', $pool);
        if (! $this->config->has($key)) {
            throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $config = $this->config->get($key);
        $count = $config['pool']['connections'] ?? 2;

        for ($i = 0; $i < $count; ++$i) {
            $this->connections[$pool][] = $this->make($config);
        }
    }

    public function getConnection(string $pool): AMQPConnection
    {
        if (isset($this->connections[$pool])) {
            return Arr::random($this->connections[$pool]);
        }

        $this->refresh($pool);
        return Arr::random($this->connections[$pool]);
    }

    public function make(array $config): AMQPConnection
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 5672;
        $user = $config['user'] ?? 'guest';
        $password = $config['password'] ?? 'guest';
        $vhost = $config['vhost'] ?? '/';

        $params = new Params(Arr::get($config, 'params', []));
        $io = new SwooleIO(
            $host,
            $port,
            $params->getConnectionTimeout(),
            $params->getReadWriteTimeout(),
            $params->getHeartbeat()
        );

        $connection = new AMQPConnection(
            $user,
            $password,
            $vhost,
            $params->isInsist(),
            $params->getLoginMethod(),
            $params->getLoginResponse(),
            $params->getLocale(),
            $io,
            $params->getHeartbeat(),
            $params->getConnectionTimeout(),
            $params->getChannelRpcTimeout()
        );

        return $connection->setLogger($this->container->get(StdoutLoggerInterface::class));
    }
}
