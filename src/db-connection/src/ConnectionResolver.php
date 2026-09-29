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

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\DbConnection\Pool\PoolFactory;
use Psr\Container\ContainerInterface;

use function Hyperf\Coroutine\defer;

class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * The default connection name.
     */
    protected string $default = 'default';

    protected PoolFactory $factory;

    public function __construct(protected ContainerInterface $container)
    {
        $this->factory = $container->get(PoolFactory::class);
    }

    /**
     * Get a database connection instance.
     *
     * When the connection is configured as lazy (databases.{name}.lazy), a LazyConnection
     * proxy is returned instead, which resolves the pooled connection on first real use.
     */
    public function connection(?string $name = null): ConnectionInterface
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
            if ($this->isLazyConnection($name)) {
                $connection = new LazyConnection($this->container, $this, $name);
                Context::set($id, $connection);
            } else {
                $connection = $this->resolveConnection($name);
            }
        }

        return $connection;
    }

    /**
     * Resolve a real connection from the pool and bind it to the coroutine context.
     * The connection is released back to the pool when the coroutine is destructed.
     */
    public function resolveConnection(string $name): ConnectionInterface
    {
        $id = $this->getContextKey($name);
        $connection = Context::get($id);
        if ($connection instanceof ConnectionInterface && ! $connection instanceof LazyConnection) {
            return $connection;
        }

        $pool = $this->factory->getPool($name);
        $connection = $pool->get();
        try {
            // PDO is initialized as an anonymous function, so there is no IO exception,
            // but if other exceptions are thrown, the connection will not return to the connection pool properly.
            $connection = $connection->getConnection();
            Context::set($id, $connection);
        } finally {
            if (Coroutine::inCoroutine()) {
                defer(function () use ($connection, $id) {
                    Context::set($id, null);
                    $connection->release();
                });
            }
        }

        return $connection;
    }

    /**
     * Get the default connection name.
     */
    public function getDefaultConnection(): string
    {
        return $this->default;
    }

    /**
     * Set the default connection name.
     */
    public function setDefaultConnection(string $name): void
    {
        $this->default = $name;
    }

    /**
     * Determine whether the connection should be resolved lazily,
     * enabled by the `databases.{name}.lazy` option, default false.
     */
    protected function isLazyConnection(string $name): bool
    {
        return (bool) $this->container->get(ConfigInterface::class)
            ->get(sprintf('databases.%s.lazy', $name), false);
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
