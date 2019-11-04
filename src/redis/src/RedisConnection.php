<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Redis;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Str;
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
    protected $config = [
        'host' => 'localhost',
        'port' => 6379,
        'auth' => null,
        'db' => 0,
        'timeout' => 0.0,
        'options' => [],
    ];

    /**
     * Current redis database.
     * @var null|int
     */
    protected $database;

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace($this->config, $config);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    /**
     * @param null|int $cursor
     * @param string $pattern
     * @param int $count
     *
     * @return array|bool
     */
    public function scan(&$cursor, $pattern = null, $count = 0)
    {
        $ret = $this->connection->scan($cursor, $this->applyPrefix($pattern), $count);
        if ($ret !== false) {
            $prefix = $this->applyPrefix('');
            array_walk($ret, function (&$key) use ($prefix) {
                $key = Str::replaceFirst($prefix, '', $key);
            });
        }
        return $ret;
    }

    /**
     * @param string $key
     * @param int $cursor
     * @param null|string $pattern
     * @param int $count
     *
     * @return array
     */
    public function hScan($key, &$cursor, $pattern = null, $count = 0)
    {
        return $this->connection->hScan($key, $cursor, $pattern, $count);
    }

    /**
     * @param string $key
     * @param int $cursor
     * @param null|string $pattern
     * @param int $count
     *
     * @return array|bool
     */
    public function zScan($key, &$cursor, $pattern = null, $count = 0)
    {
        return $this->connection->zScan($key, $cursor, $pattern, $count);
    }

    /**
     * @param string $key
     * @param int $cursor
     * @param null|string $pattern
     * @param int $count
     *
     * @return array|bool
     */
    public function sScan($key, &$cursor, $pattern = null, $count = 0)
    {
        return $this->connection->sScan($key, $cursor, $pattern, $count);
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    public function reconnect(): bool
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $auth = $this->config['auth'];
        $db = $this->config['db'];
        $timeout = $this->config['timeout'];

        $redis = new \Redis();
        if (! $redis->connect($host, $port, $timeout)) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        $options = $this->config['options'] ?? [];

        foreach ($options as $name => $value) {
            // The name is int, value is string.
            $redis->setOption($name, $value);
        }

        if (isset($auth) && $auth !== '') {
            $redis->auth($auth);
        }

        $database = $this->database ?? $db;
        if ($database > 0) {
            $redis->select($database);
        }

        $this->connection = $redis;
        $this->lastUseTime = microtime(true);

        return true;
    }

    public function close(): bool
    {
        unset($this->connection);

        return true;
    }

    public function release(): void
    {
        if ($this->database && $this->database != $this->config['db']) {
            // Select the origin db after execute select.
            $this->select($this->config['db']);
            $this->database = null;
        }
        parent::release();
    }

    public function setDatabase(?int $database): void
    {
        $this->database = $database;
    }

    /**
     * Apply prefix to the given key if necessary.
     *
     * @param string $key
     *
     * @return string
     */
    private function applyPrefix($key = ''): string
    {
        $prefix = (string) $this->connection->getOption(\Redis::OPT_PREFIX);
        return $prefix . $key;
    }
}
