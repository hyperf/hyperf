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

use Hyperf\Amqp\Connection\Connection;
use Hyperf\Contract\ConfigInterface;
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
     * @var Connection[][]
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

        $params = new Params(Arr::get($config, 'params', []));

        for ($i = 0; $i < $count; ++$i) {
            $this->connections[$pool][] = new Connection(
                $config['host'] ?? 'localhost',
                $config['port'] ?? 5672,
                $config['user'] ?? 'guest',
                $config['password'] ?? 'guest',
                $config['vhost'] ?? '/',
                $params->isInsist(),
                $params->getLoginMethod(),
                $params->getLoginResponse(),
                $params->getLocale(),
                $params->getConnectionTimeout(),
                $params->getReadWriteTimeout(),
                $params->getContext(),
                $params->isKeepalive(),
                $params->getHeartbeat()
            );
        }
    }

    public function getConnection(string $pool): Connection
    {
        if (isset($this->connections[$pool])) {
            return Arr::random($this->connections[$pool]);
        }

        $this->refresh($pool);
        return Arr::random($this->connections[$pool]);
    }
}
