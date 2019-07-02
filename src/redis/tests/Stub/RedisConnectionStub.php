<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Redis\Stub;

use Hyperf\Redis\RedisConnection;

class RedisConnectionStub extends RedisConnection
{
    public $host;

    public $port;

    public $auth;

    public $db;

    public $timeout;

    public function __call($name, $arguments)
    {
        return sprintf('db:%d name:%s argument:%s', $this->db, $name, implode(',', $arguments));
    }

    public function reconnect(): bool
    {
        $this->host = $this->config['host'] ?? 'localhost';
        $this->port = $this->config['port'] ?? 6379;
        $this->auth = $this->config['auth'] ?? null;
        $this->db = $this->config['db'] ?? 0;
        $this->timeout = $this->config['timeout'] ?? 0.0;

        return true;
    }

    public function getConnection()
    {
        return parent::getConnection();
    }

    public function select($db)
    {
        $this->db = $db;
    }
}
