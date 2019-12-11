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

namespace Hyperf\DB;

use Hyperf\DB\Pool\PoolFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Throwable;

/**
 * @method beginTransaction()
 * @method commit()
 * @method rollback()
 * @method insert(string $query, array $bindings = [])
 * @method execute(string $query, array $bindings = [])
 * @method query(string $query, array $bindings = [])
 * @method fetch(string $query, array $bindings = [])
 * @method DB connection(string $pool = 'default')
 * @method resetRecordsModified()
 * @method bool getRecordsModified()
 */
class DB
{
    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $poolName;

    public function __construct(PoolFactory $factory, string $poolName = 'default')
    {
        $this->factory = $factory;
        $this->poolName = $poolName;
    }

    public function __call($name, $arguments)
    {
        if ($name === 'connection') {
            $this->poolName = $arguments[0] ?? 'default';
            return $this;
        }

        $hasContextConnection = Context::has($this->getContextKey());
        $connection = $this->getConnection($hasContextConnection);

        try {
            $connection = $connection->getConnection();
            $result = $connection->{$name}(...$arguments);
        } catch (Throwable $exception) {
            $result = $connection->retry($exception, $name, $arguments);
        } finally {
            if (! $hasContextConnection) {
                if ($this->shouldUseSameConnection($name) || $connection->getRecordsModified()) {
                    // Should storage the connection to coroutine context, then use defer() to release the connection.
                    Context::set($this->getContextKey(), $connection);
                    defer(function () use ($connection) {
                        $this->poolName = 'default';
                        $connection->resetRecordsModified();
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

    public static function __callStatic($name, $arguments)
    {
        $container = ApplicationContext::getContainer();
        $db = $container->get(static::class);
        return $db->{$name}(...$arguments);
    }

    /**
     * Define the commands that needs same connection to execute.
     * When these commands executed, the connection will storage to coroutine context.
     */
    protected function shouldUseSameConnection(string $methodName): bool
    {
        return in_array($methodName, [
            'beginTransaction',
            'commit',
            'rollBack',
        ]);
    }

    /**
     * Get a connection from coroutine context, or from mysql connection pool.
     */
    protected function getConnection(bool $hasContextConnection): AbstractConnection
    {
        $connection = null;
        if ($hasContextConnection) {
            $connection = Context::get($this->getContextKey());
        }
        if (! $connection instanceof AbstractConnection) {
            $pool = $this->factory->getPool($this->poolName);
            $connection = $pool->get();
        }
        return $connection;
    }

    /**
     * The key to identify the connection object in coroutine context.
     */
    private function getContextKey(): string
    {
        return sprintf('db.connection.%s', $this->poolName);
    }
}
