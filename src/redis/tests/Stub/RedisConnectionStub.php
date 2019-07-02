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
        $this->host = $this->config['host'];
        $this->port = $this->config['port'];
        $this->auth = $this->config['auth'];
        $this->db = $this->config['db'];
        $this->timeout = $this->config['timeout'];

        return true;
    }

    public function select($db)
    {
        $this->db = $db;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return null|int
     */
    public function getDatabase(): ?int
    {
        return $this->database;
    }
}
