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
use Generator;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\Connection as DatabaseConnection;
use Hyperf\Database\ConnectionInterface as DbConnectionInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Query\Processors\Processor;
use Hyperf\DbConnection\Traits\DbConnection;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function Hyperf\Coroutine\defer;

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
 *
 * When release-after-use is enabled (databases.{name}.release_after_use, or
 * a runtime override via Db::enableReleaseAfterUse()), the resolved connection
 * is released back to the pool right after a query finishes (when not in
 * transaction), and the next query resolves a (possibly different) connection
 * from the pool again. The flag is checked on every release decision, so a
 * runtime toggle takes effect immediately.
 */
class LazyConnection implements ConnectionInterface, DbConnectionInterface
{
    use DbConnection;

    /**
     * The resolved pooled connection, null until the first real use,
     * or after it has been released in release-after-use mode.
     */
    protected ?DbConnectionInterface $connection = null;

    /**
     * A lightweight connection which is only used to answer metadata methods.
     */
    protected ?DatabaseConnection $metadataConnection = null;

    /**
     * Whether a write has been performed through this proxy since the
     * last (re)acquisition. Tracked by the proxy itself because the real
     * connection only exposes recordsHaveBeenModified() as a marker.
     */
    protected bool $recordsModified = false;

    /**
     * Whether the query log is enabled through this proxy.
     */
    protected bool $loggingEnabled = false;

    /**
     * The coroutine-local override of the release-after-use flag, null to
     * follow the resolver (runtime override first, then the configuration).
     */
    protected ?bool $releaseAfterUse = null;

    /**
     * Number of cursors currently streaming through this proxy.
     */
    protected int $streamingCursors = 0;

    protected bool $deferRegistered = false;

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
        if ($this->connection === null) {
            // The context always holds this proxy instead of the resolved
            // connection, and the coroutine-end cleanup is registered by
            // the proxy itself, so a runtime toggle of the release-after-use
            // flag never leaves a stale connection behind.
            $this->connection = $this->resolver->resolveConnection($this->name, false);
            $this->registerDeferOnce();
        }

        return $this->connection;
    }

    /**
     * Get the resolved pooled connection without triggering a resolution,
     * null when no pooled connection is held right now.
     */
    public function getResolvedConnection(): ?DbConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Override the release-after-use flag for this proxy only (i.e. the
     * current coroutine), with priority over the runtime override and the
     * configuration. Pass null to follow them again.
     */
    public function setReleaseAfterUse(?bool $value): void
    {
        $this->releaseAfterUse = $value;
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
            $connection = $this->connection;
            $this->connection = null;
            $this->recordsModified = false;
            $connection->release();
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

    public function select(string $query, array $bindings = [], bool $useReadPdo = true): array
    {
        try {
            return $this->getConnection()->select($query, $bindings, $useReadPdo);
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
    {
        try {
            return $this->getConnection()->selectOne($query, $bindings, $useReadPdo);
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function scalar(string $query, array $bindings = [], bool $useReadPdo = true): mixed
    {
        try {
            // Not a method of DbConnectionInterface, forward through __call.
            return $this->__call(__FUNCTION__, func_get_args());
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function cursor(string $query, array $bindings = [], bool $useReadPdo = true): Generator
    {
        $connection = $this->getConnection();
        // While a cursor is streaming, nested statements forwarded through this
        // proxy must not release the connection: the statement being iterated
        // still depends on it, and a released connection could be taken by
        // another coroutine, which breaks unbuffered cursors.
        ++$this->streamingCursors;
        try {
            // The inner cursor executes the statement on the first iteration,
            // so the connection can only be released after the generator is
            // exhausted or closed.
            yield from $connection->cursor($query, $bindings, $useReadPdo);
        } finally {
            --$this->streamingCursors;
            $this->releaseAfterUse();
        }
    }

    public function insert(string $query, array $bindings = []): bool
    {
        $this->recordsModified = true;
        // Never released here: Processor::processInsertGetId() performs insert()
        // and getPdo()->lastInsertId() as two separate calls which must share
        // one and the same connection.
        return $this->getConnection()->insert($query, $bindings);
    }

    public function update(string $query, array $bindings = []): int
    {
        $this->recordsModified = true;
        try {
            return $this->getConnection()->update($query, $bindings);
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function delete(string $query, array $bindings = []): int
    {
        $this->recordsModified = true;
        try {
            return $this->getConnection()->delete($query, $bindings);
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function statement(string $query, array $bindings = []): bool
    {
        $this->recordsModified = true;
        try {
            return $this->getConnection()->statement($query, $bindings);
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function affectingStatement(string $query, array $bindings = []): int
    {
        $this->recordsModified = true;
        try {
            return $this->getConnection()->affectingStatement($query, $bindings);
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function unprepared(string $query): bool
    {
        $this->recordsModified = true;
        try {
            return $this->getConnection()->unprepared($query);
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        $this->recordsModified = true;
        try {
            return $this->getConnection()->transaction($callback, $attempts);
        } finally {
            // The inner commit/rollBack runs on the real connection and never
            // passes this proxy, so check the release condition here.
            $this->releaseAfterUse();
        }
    }

    public function beginTransaction(): void
    {
        $this->recordsModified = true;
        // Never released here: the connection is held for the whole transaction.
        $this->getConnection()->beginTransaction();
    }

    public function commit(): void
    {
        try {
            $this->getConnection()->commit();
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function rollBack($toLevel = null): void
    {
        try {
            // The $toLevel argument is not declared on DbConnectionInterface,
            // forward through __call.
            $this->__call(__FUNCTION__, func_get_args());
        } finally {
            $this->releaseAfterUse();
        }
    }

    public function transactionLevel(): int
    {
        return $this->connection?->transactionLevel() ?? 0;
    }

    /**
     * Determine whether the connection is in a transaction,
     * without resolving the pooled connection.
     */
    public function isTransaction(): bool
    {
        return $this->transactionLevel() > 0;
    }

    public function enableQueryLog()
    {
        $this->loggingEnabled = true;
        // Not a method of DbConnectionInterface, forward through __call.
        $this->__call(__FUNCTION__, func_get_args());
    }

    public function disableQueryLog()
    {
        $this->loggingEnabled = false;
        $this->__call(__FUNCTION__, func_get_args());
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
     * Release the resolved connection back to the pool right after a use,
     * so that the pool slot is not occupied for the rest of the coroutine.
     *
     * The connection is kept when it is still needed:
     * in a transaction, when the query log is enabled, while a cursor is
     * streaming, or after a write on a sticky read/write connection
     * (following reads must hit the write PDO).
     */
    protected function releaseAfterUse(): void
    {
        if (! $this->isReleaseAfterUse()) {
            return;
        }
        $connection = $this->connection;
        if (! $connection instanceof ConnectionInterface) {
            return;
        }
        if ($connection->transactionLevel() > 0 || $this->loggingEnabled || $this->streamingCursors > 0) {
            return;
        }
        if ($this->recordsModified && $this->getConfig('sticky')) {
            return;
        }
        $this->release();
    }

    /**
     * Determine whether the pooled connection should be released right after
     * a use. The coroutine-local override set by `setReleaseAfterUse()` takes
     * priority, then the runtime override on the resolver, then the
     * `databases.{name}.release_after_use` configuration.
     */
    protected function isReleaseAfterUse(): bool
    {
        return $this->releaseAfterUse ?? $this->resolver->isReleaseAfterUse($this->name);
    }

    /**
     * Register the coroutine-end cleanup, which releases the connection still
     * held and clears the context. Registered once per proxy (i.e. once per
     * coroutine), in both lazy and release-after-use mode.
     */
    protected function registerDeferOnce(): void
    {
        if ($this->deferRegistered || ! Coroutine::inCoroutine()) {
            return;
        }
        $this->deferRegistered = true;
        $id = $this->resolver->getContextKey($this->name);
        defer(function () use ($id) {
            Context::set($id, null);
            // Releases only when a connection is still held, mid-coroutine
            // releases have cleared it already, so no double release here.
            $this->release();
        });
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
