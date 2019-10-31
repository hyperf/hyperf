<?php

namespace Hyperf\DB;


use Hyperf\DB\Pool\AbstractPool;
use Hyperf\DB\Pool\PoolFactory;
use Hyperf\DB\Pool\SwooleMySqlPool;
use Hyperf\Pool\Connection;
use Hyperf\Utils\Context;
use Swoole\Coroutine\MySQL;

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

        $result = $connection->{$name}(...$arguments);

        return $result;
    }


    /**
     * Get a connection from coroutine context, or from redis connectio pool.
     * @param mixed $hasContextConnection
     */
    private function getConnection($hasContextConnection): Connection
    {
        $connection = null;
        if ($hasContextConnection) {
            $connection = Context::get($this->getContextKey());
        }
        if (!$connection instanceof Connection) {
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
}