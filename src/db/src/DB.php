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
use Hyperf\Utils\Context;
use Throwable;

/**
 * @method beginTransaction()
 * @method commit()
 * @method rollback()
 * @method getErrorCode()
 * @method getErrorInfo()
 * @method getLastInsertId()
 * @method prepare(string $sql, array $data = [], array $options = [])
 * @method query(string $sql)
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
        $hasContextConnection = $this->hasContextConnection($name);
        $connection = $this->getConnection($hasContextConnection);

        try {
            $connection = $connection->getConnection();
            $result = $connection->{$name}(...$arguments);
        } catch (Throwable $exception) {
            $result = $connection->retry($exception, $name, $arguments);
        } finally {
            if (! $hasContextConnection) {
                $connection->release();
            }
        }

        return $result;
    }

    protected function hasContextConnection($name): bool
    {
        $hasContextConnection = Context::has($this->getContextKey());
        if (! $hasContextConnection) {
            if (in_array($name, ['beginTransaction', 'commit', 'rollBack'])) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Get a connection from coroutine context, or from mysql connectio pool.
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
            if ($hasContextConnection) {
                Context::set($this->getContextKey(), $connection);
                defer(function () use ($connection) {
                    $connection->release();
                });
            }
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
