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

use Closure;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\DbConnection\Pool\PoolFactory;
use LogicException;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Coroutine\defer;

class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * The default connection name.
     */
    protected string $default = 'default';

    protected PoolFactory $factory;

    /**
     * The runtime overrides of the `release_after_use` option, keyed by
     * connection name. An override takes priority over the configuration,
     * and applies to all coroutines from the moment it is set.
     */
    protected array $releaseAfterUseOverrides = [];

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
     * Each acquisition creates a new lease; it never adopts a Context connection
     * whose cleanup already belongs to another session.
     * @internal
     */
    public function acquireLease(string $name): ConnectionLease
    {
        $connection = $this->factory->getPool($name)->get();
        try {
            if (! $connection instanceof Connection) {
                throw new LogicException('The database pool must return a db-connection Connection.');
            }
            $connection->getConnection();
            return new ConnectionLease($connection);
        } catch (Throwable $exception) {
            $connection->release();
            throw $exception;
        }
    }

    /**
     * Use the current connection for a bounded group of session commands.
     */
    public function withConnection(Closure $callback, ?string $name = null): mixed
    {
        $connection = $this->connection($name);
        if ($connection instanceof LazyConnection) {
            return $connection->withConnection($callback);
        }
        return $callback($connection);
    }

    /**
     * A temporary release policy belongs to this coroutine, never to the worker.
     * Enable it before obtaining an eager connection; existing eager handles cannot
     * be replaced safely while the caller may still retain them.
     */
    public function withReleaseAfterUse(bool $value, Closure $callback, ?string $name = null): mixed
    {
        $name ??= $this->getDefaultConnection();
        $existing = Context::get($this->getContextKey($name));
        if ($existing instanceof ConnectionInterface && ! $existing instanceof LazyConnection) {
            throw new LogicException('Set a scoped release policy before obtaining an eager connection, or enable lazy in configuration.');
        }
        $key = $this->getPolicyContextKey($name);
        $previous = Context::get($key);
        Context::set($key, $value);
        try {
            $connection = $this->connection($name);
            if (! $connection instanceof LazyConnection) {
                throw new LogicException('Scoped release policies require a logical connection.');
            }
        } finally {
            if ($previous === null) {
                Context::destroy($key);
            } else {
                Context::set($key, $previous);
            }
        }
        return $connection->withReleaseAfterUse($value, $callback);
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
     * The key to identify the connection object in coroutine context.
     * @param mixed $name
     */
    public function getContextKey($name): string
    {
        return sprintf('database.connection.%s', $name);
    }

    /**
     * Override the `release_after_use` option at runtime, with priority
     * over the configuration. Applies to logical sessions in this worker on their next release decision.
     * Existing eager handles retain their original lifecycle. Use `resetReleaseAfterUse()` to follow the config again.
     */
    public function setReleaseAfterUse(string $name, bool $value): void
    {
        $this->releaseAfterUseOverrides[$name] = $value;
    }

    /**
     * Remove the runtime override of the `release_after_use` option,
     * so the connection follows the configuration again.
     */
    public function resetReleaseAfterUse(string $name): void
    {
        unset($this->releaseAfterUseOverrides[$name]);
    }

    /**
     * Determine whether the pooled connection should be released right after
     * each use (when not in transaction). A runtime override set by
     * `setReleaseAfterUse()` takes priority over the
     * `databases.{name}.release_after_use` option, default false.
     */
    public function isReleaseAfterUse(string $name): bool
    {
        $local = Context::get($this->getPolicyContextKey($name));
        if (is_bool($local)) {
            return $local;
        }
        if (array_key_exists($name, $this->releaseAfterUseOverrides)) {
            return $this->releaseAfterUseOverrides[$name];
        }
        return filter_var($this->container->get(ConfigInterface::class)
            ->get(sprintf('databases.%s.release_after_use', $name), false), FILTER_VALIDATE_BOOL);
    }

    /**
     * Keep the pre-existing eager API when neither lazy nor scoped policy is enabled.
     */
    protected function resolveConnection(string $name): ConnectionInterface
    {
        $lease = $this->acquireLease($name);
        $connection = $lease->getConnection();
        $id = $this->getContextKey($name);
        Context::set($id, $connection);
        if (Coroutine::inCoroutine()) {
            defer(function () use ($lease, $connection, $id) {
                if (Context::get($id) === $connection) {
                    Context::destroy($id);
                }
                $lease->release();
            });
        }
        return $connection;
    }

    /**
     * Determine whether the connection should be resolved lazily,
     * enabled by the `databases.{name}.lazy` option, default false.
     * The `databases.{name}.release_after_use` option implies lazy.
     */
    protected function getPolicyContextKey(string $name): string
    {
        return sprintf('database.release_after_use.%s', $name);
    }

    protected function isLazyConnection(string $name): bool
    {
        return Context::has($this->getPolicyContextKey($name))
            || filter_var($this->container->get(ConfigInterface::class)
                ->get(sprintf('databases.%s.lazy', $name), false), FILTER_VALIDATE_BOOL)
            || $this->isReleaseAfterUse($name);
    }
}
