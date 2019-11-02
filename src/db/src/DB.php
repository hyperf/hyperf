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

/**
 * Class DB.
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
    protected $poolName = 'default';

    public function __construct(PoolFactory $factory)
    {
        $this->factory = $factory;
    }

    public function __call($name, $arguments)
    {
        // Get a connection from coroutine context or connection pool.
        $count = 0;
        if (Context::has('transactionConnection')) {
            $connection = Context::get('transactionConnection');
        } else {
            $hasContextConnection = Context::has($this->getContextKey());
            $connection = $this->getConnection($hasContextConnection);
        }

        if ($name == 'beginTransaction') {
            Context::set('transactionConnection', $connection);
            $count = $this->addTransactionConnectionCount();
        }

        if ($name == 'commit' || $name == 'rollback') {
            $count = $this->cutTransactionConnectionCount();
            if ($count == 0) {
                Context::destroy('transactionConnection');
            }
        }

        $result = null;
        if ($count === 0 && $name != 'commit' && $name != 'rollback') {
            $result = $connection->{$name}(...$arguments);
        }

        return $result;
    }

    /**
     * Get a connection from coroutine context, or from mysql connectio pool.
     * @param mixed $hasContextConnection
     */
    private function getConnection($hasContextConnection): AbstractConnection
    {
        $connection = null;
        if ($hasContextConnection) {
            $connection = Context::get($this->getContextKey());
        }
        if (! $connection instanceof AbstractConnection) {
            $pool = $this->factory->getPool($this->poolName);
            $connection = $pool->get()->getConnection();
        }
        return $connection;
    }

    /**
     * The key to identify the connection object in coroutine context.
     */
    private function getContextKey(): string
    {
        return sprintf('database.%s', $this->poolName);
    }

    private function addTransactionConnectionCount()
    {
        $count = Context::get('transactionConnectionCount') + 1;
        Context::set('transactionConnectionCount', $count);
        return $count;
    }

    private function cutTransactionConnectionCount()
    {
        $count = Context::get('transactionConnectionCount') - 1;
        Context::set('transactionConnectionCount', $count);
        return $count;
    }
}
