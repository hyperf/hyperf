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
 * Class DB
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
        $hasContextConnection = Context::has($this->getContextKey());
        $connection = $this->getConnection($hasContextConnection);
        var_dump($name);
        switch ($name) {
            case 'beginTransaction':
                Context::set($this->getContextKey(), $connection);
                $transctionManager = new TransactionManager();
                $result = $transctionManager->beginTransaction();
                break;
            case 'commit':
            case 'rollback':
                $transctionManager = new TransactionManager();
                $result = $transctionManager->$name();
                break;
            default:
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
        if (!$connection instanceof AbstractConnection) {
            $pool = $this->factory->getPool($this->poolName);
            Context::set('poolId', $pool->getCurrentConnections());
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

    private function getPoolId()
    {
        return Context::get('poolId');
    }


}