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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Database\Connection as DatabaseConnection;
use Hyperf\Database\ConnectionInterface as DbConnectionInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\DbConnection\Traits\DbConnection;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

/**
 * A lazy proxy of the pooled connection.
 *
 * It does not occupy a pooled connection until a method that really needs the
 * connection is called (select/insert/update/delete/transaction ...).
 *
 * Metadata-only methods (grammar, processor, raw, config getters ...) are answered
 * by a lightweight connection created by ConnectionFactory, which neither touches
 * the pool nor establishes any physical connection (the PDO is a closure there).
 * Because the metadata connection is made by the same factory with the same config
 * as the pooled one, the grammar and processor are always identical.
 *
 * Any method that is not specially handled resolves the pooled connection first
 * and then forwards the call, which falls back to the eager behavior.
 */
class LazyConnection implements ConnectionInterface, DbConnectionInterface
{
    use DbConnection;

    /**
     * The resolved pooled connection, null until the first real use.
     */
    protected ?DbConnectionInterface $connection = null;

    /**
     * A lightweight connection which is only used to answer metadata methods.
     */
    protected ?DatabaseConnection $metadataConnection = null;

    public function __construct(
        protected ContainerInterface $container,
        protected ConnectionResolver $resolver,
        protected string $name
    ) {
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->getConnection()->{$name}(...$arguments);
    }

    /**
     * Resolve and return the real pooled connection.
     */
    public function getConnection(): DbConnectionInterface
    {
        return $this->connection ??= $this->resolver->resolveConnection($this->name);
    }

    public function reconnect(): bool
    {
        if ($this->connection instanceof ConnectionInterface) {
            return $this->connection->reconnect();
        }
        // The pooled connection has never been resolved,
        // the first real use will get a fresh one from the pool.
        return true;
    }

    public function check(): bool
    {
        if ($this->connection instanceof ConnectionInterface) {
            return $this->connection->check();
        }
        return true;
    }

    public function close(): bool
    {
        if ($this->connection instanceof ConnectionInterface) {
            return $this->connection->close();
        }
        return true;
    }

    /**
     * Release the connection back to the pool.
     * It is a no-op when the pooled connection has never been resolved.
     */
    public function release(): void
    {
        if ($this->connection instanceof ConnectionInterface) {
            $this->connection->release();
        }
    }

    /**
     * Begin a fluent query against a database table.
     *
     * The builder is bound to this lazy proxy, so the pooled connection
     * is resolved when the query is executed, not when it is built.
     * @param mixed $table
     */
    public function table($table): Builder
    {
        return $this->query()->from($table);
    }

    /**
     * Get a new query builder instance bound to this lazy proxy.
     */
    public function query(): Builder
    {
        return new Builder($this, $this->getQueryGrammar(), $this->getPostProcessor());
    }

    /**
     * Get a new expression instance, which does not need the connection.
     * @param mixed $value
     */
    public function raw($value): Expression
    {
        return $this->getMetadataConnection()->raw($value);
    }

    public function getQueryGrammar()
    {
        return $this->getMetadataConnection()->getQueryGrammar();
    }

    public function getPostProcessor(): Processor
    {
        return $this->getMetadataConnection()->getPostProcessor();
    }

    public function getName()
    {
        return $this->getMetadataConnection()->getName();
    }

    public function getConfig($option = null)
    {
        return $this->getMetadataConnection()->getConfig($option);
    }

    public function getDriverName()
    {
        return $this->getMetadataConnection()->getDriverName();
    }

    public function getDatabaseName()
    {
        return $this->getMetadataConnection()->getDatabaseName();
    }

    public function getTablePrefix(): string
    {
        return $this->getMetadataConnection()->getTablePrefix();
    }

    /**
     * Make a lightweight connection via ConnectionFactory to answer metadata
     * methods. It never touches the pool, and the PDO inside is a closure,
     * so no physical connection is established either.
     */
    protected function getMetadataConnection(): DatabaseConnection
    {
        if ($this->metadataConnection === null) {
            $config = $this->container->get(ConfigInterface::class);
            $key = sprintf('databases.%s', $this->name);
            if (! $config->has($key)) {
                throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
            }
            $options = $config->get($key);
            // Keep consistent with DbPool, which rewrites the `name` of the configuration item.
            $options['name'] = $this->name;
            $connection = $this->container->get(ConnectionFactory::class)->make($options);
            if (! $connection instanceof DatabaseConnection) {
                throw new InvalidArgumentException(sprintf('The connection of config[%s] must be an instance of %s.', $key, DatabaseConnection::class));
            }
            $this->metadataConnection = $connection;
        }

        return $this->metadataConnection;
    }
}
