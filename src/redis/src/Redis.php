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
namespace Hyperf\Redis;

use Hyperf\Redis\Exception\InvalidRedisConnectionException;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Utils\Context;

/**
 * @mixin \Redis
 */
class Redis
{
    use ScanCaller;

    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $poolName = 'default';

    public function __construct(PoolFactory $factory)
    {
        $this->factory = $factory;
    }

    public function __call($name, $arguments)
    {
        // Get a connection from coroutine context or connection pool.
        $hasContextConnection = Context::has($this->getContextKey());
        $connection = $this->getConnection($hasContextConnection);

        try {
            $connection = $connection->getConnection();
            // Execute the command with the arguments.
            $result = $connection->{$name}(...$arguments);
        } finally {
            // Release connection.
            if (! $hasContextConnection) {
                if ($this->shouldUseSameConnection($name)) {
                    if ($name === 'select' && $db = $arguments[0]) {
                        $connection->setDatabase((int) $db);
                    }
                    // Should storage the connection to coroutine context, then use defer() to release the connection.
                    Context::set($this->getContextKey(), $connection);
                    defer(function () use ($connection) {
                        Context::set($this->getContextKey(), null);
                        $connection->release();
                    });
                } else {
                    // Release the connection after command executed.
                    $connection->release();
                }
            }
        }

        return $result;
    }

    /**
     * Define the commands that needs same connection to execute.
     * When these commands executed, the connection will storage to coroutine context.
     */
    private function shouldUseSameConnection(string $methodName): bool
    {
        return in_array($methodName, [
            'multi',
            'pipeline',
            'select',
        ]);
    }

    /**
     * Get a connection from coroutine context, or from redis connection pool.
     * @param mixed $hasContextConnection
     */
    private function getConnection($hasContextConnection): RedisConnection
    {
        $connection = null;
        if ($hasContextConnection) {
            $connection = Context::get($this->getContextKey());
        }
        if (! $connection instanceof RedisConnection) {
            $pool = $this->factory->getPool($this->poolName);
            $connection = $pool->get();
        }
        if (! $connection instanceof RedisConnection) {
            throw new InvalidRedisConnectionException('The connection is not a valid RedisConnection.');
        }
        return $connection;
    }

    /**
     * The key to identify the connection object in coroutine context.
     */
    private function getContextKey(): string
    {
        return sprintf('redis.connection.%s', $this->poolName);
    }
}
