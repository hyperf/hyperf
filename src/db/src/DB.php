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
namespace Hyperf\DB;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DB\Exception\DriverNotFoundException;
use Hyperf\DB\Pool\PoolFactory;
use Throwable;

use function Hyperf\Coroutine\defer;
use function Hyperf\Support\make;

/**
 * @method beginTransaction()
 * @method commit()
 * @method rollback()
 * @method insert(string $query, array $bindings = [])
 * @method execute(string $query, array $bindings = [])
 * @method query(string $query, array $bindings = [])
 * @method fetch(string $query, array $bindings = [])
 * @method run(Closure $closure)
 */
class DB
{
    public function __construct(protected PoolFactory $factory, protected string $poolName = 'default')
    {
    }

    public function __call($name, $arguments)
    {
        $container = ApplicationContext::getContainer();
        $config = $container->get(ConfigInterface::class);
        $isDeferRelease = $config->get(sprintf('db.%s.defer_release', $this->poolName), false);

        $hasContextConnection = Context::has($this->getContextKey());
        $connection = $this->getConnection($hasContextConnection);

        try {
            $connection = $connection->getConnection();
            $result = $connection->{$name}(...$arguments);
        } catch (Throwable $exception) {
            $result = $connection->retry($exception, $name, $arguments);
        } finally {
            if (! $hasContextConnection) {
                if ($this->shouldUseSameConnection($name)) {
                    // Should storage the connection to coroutine context, then use defer() to release the connection.
                    Context::set($contextKey = $this->getContextKey(), $connection);
                    defer(function () use ($connection, $contextKey) {
                        Context::set($contextKey, null);
                        $connection->release();
                    });
                } elseif ($isDeferRelease) {
                    // Release the connection after coroutine is exit.
                    defer(function () use ($connection) {
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
     * Make a new connection with the pool name.
     */
    public static function connection(string $poolName): self
    {
        return make(static::class, [
            'poolName' => $poolName,
        ]);
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
        if (! $connection instanceof AbstractConnection) {
            throw new DriverNotFoundException(sprintf('%s must instance of %s', get_class($connection), AbstractConnection::class));
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
