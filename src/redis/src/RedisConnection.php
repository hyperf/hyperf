<?php

namespace Hyperf\Redis;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;

class RedisConnection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var \Redis
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = $config;

        $this->reconnect();
    }

    public function getConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    public function reconnect(): bool
    {
        $host = $this->config['host'] ?? 'localhost';
        $port = $this->config['port'] ?? 6379;
        $auth = $this->config['auth'] ?? null;
        $db = $this->config['db'] ?? 0;

        $redis = new \Redis();
        if (!$redis->connect($host, $port)) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        if (isset($auth)) {
            $redis->auth($auth);
        }

        if ($db > 0) {
            $redis->select($db);
        }

        $this->connection = $redis;
        return true;
    }

    public function __call($name, $arguments)
    {
        return $this->connection->$name(...$arguments);
    }

    public function check(): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }
}