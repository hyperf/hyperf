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
     * Resolve a real connection from the pool and bind it to the coroutine context.
     * The connection is released back to the pool when the coroutine is destructed.
     *
     * In unmanaged mode the context and the defer callback are skipped, the caller
     * (e.g. LazyConnection in release-after-use mode) manages the lifecycle itself.
     */
    public function resolveConnection(string $name, bool $managed = true): ConnectionInterface
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
            if ($managed) {
                Context::set($id, $connection);
            }
        } catch (Throwable $exception) {
            $connection->release();
            throw $exception;
        }

        if ($managed && Coroutine::inCoroutine()) {
            defer(function () use ($connection, $id) {
                Context::set($id, null);
                $connection->release();
            });
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
     * The key to identify the connection object in coroutine context.
     * @param mixed $name
     */
    public function getContextKey($name): string
    {
        return sprintf('database.connection.%s', $name);
    }

    /**
     * Override the `release_after_use` option at runtime, with priority
     * over the configuration. Applies to all coroutines from the moment
     * it is set. Use `resetReleaseAfterUse()` to follow the config again.
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
        if (array_key_exists($name, $this->releaseAfterUseOverrides)) {
            return $this->releaseAfterUseOverrides[$name];
        }
        return (bool) $this->container->get(ConfigInterface::class)
            ->get(sprintf('databases.%s.release_after_use', $name), false);
    }

    /**
     * Determine whether the connection should be resolved lazily,
     * enabled by the `databases.{name}.lazy` option, default false.
     * The `databases.{name}.release_after_use` option implies lazy.
     */
    protected function isLazyConnection(string $name): bool
    {
        return (bool) $this->container->get(ConfigInterface::class)
            ->get(sprintf('databases.%s.lazy', $name), false)
            || $this->isReleaseAfterUse($name);
    }
}
