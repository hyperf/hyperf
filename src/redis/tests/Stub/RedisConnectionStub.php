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

namespace HyperfTest\Redis\Stub;

use Hyperf\Pool\Pool;
use Hyperf\Redis\RedisConnection;
use Psr\Container\ContainerInterface;

class RedisConnectionStub extends RedisConnection
{
    public $host;

    public $port;

    public $auth;

    public $db;

    public $timeout;

    public $id;

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool, $config);
        $this->id = uniqid();
    }

    public function __call($name, $arguments)
    {
        return sprintf('db:%d name:%s argument:%s', $this->db, $name, implode(',', $arguments));
    }

    public function reconnect(): bool
    {
        $this->host = $this->config['host'];
        $this->port = $this->config['port'];
        $this->auth = $this->config['auth'];
        $this->db = $this->config['db'];
        $this->timeout = $this->config['timeout'];

        $this->lastUseTime = microtime(true);

        if ($this->config['cluster']['enable'] ?? false) {
            $this->createRedisCluster();
        }

        return true;
    }

    public function select($db)
    {
        $this->db = $db;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getDatabase(): ?int
    {
        return $this->database;
    }
}
