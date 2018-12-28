<?php

namespace Hyperf\Pool;


use Hyperf\Pool\Exception\InvalidArgumentException;
use Swoole\Coroutine\Channel;

abstract class ConnectionPool
{

    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @var array
     */
    protected static $options = [];

    abstract protected static function createConnection();

    protected static function getConnection(string $key, int $timeout)
    {
        return static::getPool($key)->pop($timeout);
    }

    protected static function releaseConnection(string $key, $connection)
    {
        return static::getPool($key)->push($connection);
    }

    protected static function initPool(string $key, PoolOption $option)
    {
        if (! $option->getMaxConnections() > 0) {
            throw new InvalidArgumentException('Missing max connections of option.');
        }
        $channel = new Channel($option->getMaxConnections());
        if ($option->getMinConnections() > 0) {
            for ($i = 0; $i < $option->getMinConnections(); $i++) {
                $channel->push(static::createConnection());
            }
        }
        static::$container[$key] = $channel;
        static::$options[$key] = $option;
    }

    protected static function getPool(string $key): Channel
    {
        return static::$container[$key];
    }

    protected static function removePool(string $key)
    {
        unset(static::$container[$key]);
    }

}