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
namespace Hyperf\DbConnection;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default = 'default';

    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->factory = $container->get(PoolFactory::class);
    }

    /**
     * Get a database connection instance.
     *
     * @param string $name
     * @return ConnectionInterface
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $connection = null;
        $id = $this->getContextKey($name);
        if (Context::has($id)) {
            $connection = Context::get($id);
        }

        if (! $connection instanceof ConnectionInterface) {
            $pool = $this->factory->getPool($name);
            $connection = $pool->get();
            try {
                // PDO is initialized as an anonymous function, so there is no IO exception,
                // but if other exceptions are thrown, the connection will not return to the connection pool properly.
                $connection = $connection->getConnection();
                Context::set($id, $connection);
            } finally {
                if (Coroutine::inCoroutine()) {
                    defer(function () use ($connection) {
                        $connection->release();
                    });
                }
            }
        }

        return $connection;
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }

    /**
     * The key to identify the connection object in coroutine context.
     * @param mixed $name
     */
    private function getContextKey($name): string
    {
        return sprintf('database.connection.%s', $name);
    }
}
