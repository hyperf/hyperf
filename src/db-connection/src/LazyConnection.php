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
use Hyperf\Collection\Arr;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\Connection as DatabaseConnection;
use Hyperf\Database\ConnectionInterface as DbConnectionInterface;
use Hyperf\Database\ConnectionMetadata;
use Hyperf\Database\ConnectionOperationInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Database\Query\Processors\Processor;
use InvalidArgumentException;
use LogicException;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Coroutine\defer;

/**
 * A coroutine-local logical connection. Metadata belongs to this session;
 * physical connections are borrowed through a single-owner lease.
 */
class LazyConnection implements ConnectionInterface, DbConnectionInterface, ConnectionOperationInterface
{
    private ?ConnectionLease $lease = null;

    private ?ConnectionMetadata $metadata = null;

    private int $owner;

    private int $operations = 0;

    private int $pins = 0;

    private int $pretendDepth = 0;

    private bool $escaped = false;

    private bool $opaqueState = false;

    private bool $closed = false;

    private bool $loggingQueries = false;

    private array $queryLog = [];

    private ?bool $releaseAfterUse = null;

    public function __construct(
        private ContainerInterface $container,
        private ConnectionResolver $resolver,
        private string $name
    ) {
        $this->owner = Coroutine::id();
        if (Coroutine::inCoroutine()) {
            // Late defer callbacks can create fresh sessions: the coroutine engine
            // must also execute cleanup callbacks registered while draining defer.
            defer(function () {
                $this->closed = true;
                $id = $this->resolver->getContextKey($this->name);
                if (Context::get($id) === $this) {
                    Context::destroy($id);
                }
                // An abandoned stream must never return its active PDO for reuse.
                $this->releaseLease($this->operations > 0);
            });
        }
    }

    /**
     * Unknown driver APIs may return connection-bound handles or change session
     * state. Keep the lease until the coroutine ends unless explicitly scoped.
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->pinEscapedHandle();
        return $this->runOperation(function ($connection) use ($name, $arguments) {
            $result = $connection->{$name}(...$arguments);
            return $result === $connection ? $this : $result;
        });
    }

    /**
     * Exposing the pooled connection pins it. Do not retain it past the scope.
     */
    public function getConnection(): DbConnectionInterface
    {
        $this->pinEscapedHandle();
        return $this->borrow()->getConnection();
    }

    /**
     * Diagnostic only; do not use this handle to execute commands or release it.
     */
    public function getResolvedConnection(): ?DbConnectionInterface
    {
        $this->assertUsable();
        return $this->lease?->getConnection();
    }

    public function setReleaseAfterUse(?bool $value): void
    {
        $this->assertUsable();
        $this->releaseAfterUse = $value;
    }

    /**
     * Apply a coroutine-local policy and restore it, including on exceptions.
     */
    public function withReleaseAfterUse(bool $value, Closure $callback): mixed
    {
        $this->assertUsable();
        $previous = $this->releaseAfterUse;
        $this->releaseAfterUse = $value;
        try {
            return $callback($this);
        } finally {
            $this->releaseAfterUse = $previous;
            $this->releaseIfIdle();
        }
    }

    /**
     * Pin a connection for a multi-command session operation, without a transaction.
     * Handles obtained in the callback must not escape its scope.
     */
    public function withConnection(Closure $callback): mixed
    {
        $this->assertUsable();
        ++$this->pins;
        try {
            return $this->runOperation(fn () => $callback($this));
        } finally {
            --$this->pins;
            $this->releaseIfIdle();
        }
    }

    /**
     * @internal executes a complete driver operation, including callbacks and ID retrieval
     */
    public function runOperation(Closure $callback): mixed
    {
        $this->assertUsable();
        ++$this->operations;
        try {
            return $callback($this->borrow()->getDatabaseConnection());
        } finally {
            --$this->operations;
            $this->releaseIfIdle();
        }
    }

    public function reconnect(): bool
    {
        $this->assertUsable();
        if ($this->operations > 0 || $this->transactionLevel() > 0) {
            throw new LogicException('Cannot reconnect during a database operation or transaction.');
        }
        if ($this->lease !== null) {
            $this->releaseLease(true);
            $this->borrow();
        }
        return true;
    }

    public function check(): bool
    {
        $this->assertUsable();
        return $this->lease?->getConnection()->check() ?? true;
    }

    public function close(): bool
    {
        $this->assertUsable();
        if ($this->operations > 0 || $this->transactionLevel() > 0) {
            throw new LogicException('Cannot close during a database operation or transaction.');
        }
        $this->closed = true;
        $this->releaseLease(true);
        $id = $this->resolver->getContextKey($this->name);
        if (Context::get($id) === $this) {
            Context::destroy($id);
        }
        return true;
    }

    public function release(): void
    {
        $this->assertUsable();
        if ($this->operations > 0 || $this->pins > 0 || $this->transactionLevel() > 0) {
            throw new LogicException('Cannot release a connection while it is in use.');
        }
        $this->escaped = false;
        $this->releaseLease();
    }

    public function table($table): Builder
    {
        return $this->query()->from($table);
    }

    public function query(): Builder
    {
        $metadata = $this->getMetadata();
        return new $metadata->builderClass($this, $metadata->grammar, $metadata->processor);
    }

    public function raw($value): Expression
    {
        $this->assertUsable();
        return new Expression($value);
    }

    public function select(string $query, array $bindings = [], bool $useReadPdo = true): array
    {
        return $this->runOperation(fn ($connection) => $connection->select($query, $bindings, $useReadPdo));
    }

    public function selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
    {
        return $this->runOperation(fn ($connection) => $connection->selectOne($query, $bindings, $useReadPdo));
    }

    public function scalar(string $query, array $bindings = [], bool $useReadPdo = true): mixed
    {
        return $this->callDriver('scalar', [$query, $bindings, $useReadPdo]);
    }

    public function selectFromWriteConnection(string $query, array $bindings = []): array
    {
        return $this->select($query, $bindings, false);
    }

    public function insert(string $query, array $bindings = []): bool
    {
        return $this->runOperation(fn ($connection) => $connection->insert($query, $bindings));
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->runOperation(fn ($connection) => $connection->update($query, $bindings));
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->runOperation(fn ($connection) => $connection->delete($query, $bindings));
    }

    public function statement(string $query, array $bindings = []): bool
    {
        return $this->runOperation(fn ($connection) => $connection->statement($query, $bindings));
    }

    public function affectingStatement(string $query, array $bindings = []): int
    {
        return $this->runOperation(fn ($connection) => $connection->affectingStatement($query, $bindings));
    }

    public function unprepared(string $query): bool
    {
        return $this->runOperation(fn ($connection) => $connection->unprepared($query));
    }

    public function prepareBindings(array $bindings): array
    {
        return $this->runOperation(fn ($connection) => $connection->prepareBindings($bindings));
    }

    public function cursor(string $query, array $bindings = [], bool $useReadPdo = true): Generator
    {
        $this->assertUsable();
        ++$this->operations;
        try {
            foreach ($this->borrow()->getDatabaseConnection()->cursor($query, $bindings, $useReadPdo) as $row) {
                $this->assertUsable();
                yield $row;
                // Check before the inner generator fetches another row.
                $this->assertUsable();
            }
        } finally {
            --$this->operations;
            if (! $this->closed) {
                $this->releaseIfIdle();
            }
        }
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        return $this->runOperation(fn ($connection) => $connection->transaction(fn () => $callback($this), $attempts));
    }

    public function beginTransaction(): void
    {
        $this->runOperation(fn ($connection) => $connection->beginTransaction());
    }

    public function commit(): void
    {
        if ($this->transactionLevel() === 0) {
            throw new LogicException('No active transaction to commit.');
        }
        $this->runOperation(fn ($connection) => $connection->commit());
    }

    public function rollBack($toLevel = null): void
    {
        if ($this->transactionLevel() === 0) {
            throw new LogicException('No active transaction to roll back.');
        }
        $this->callDriver('rollBack', [$toLevel]);
    }

    public function transactionLevel(): int
    {
        $this->assertUsable();
        return $this->lease?->getDatabaseConnection()->transactionLevel() ?? 0;
    }

    public function isTransaction(): bool
    {
        return $this->transactionLevel() > 0;
    }

    public function pretend(Closure $callback): array
    {
        ++$this->pretendDepth;
        try {
            return $this->runOperation(fn ($connection) => $connection->pretend(fn () => $callback($this)));
        } finally {
            --$this->pretendDepth;
        }
    }

    public function pretending(): bool
    {
        return $this->pretendDepth > 0;
    }

    public function enableQueryLog(): void
    {
        $this->assertUsable();
        $this->loggingQueries = true;
    }

    public function disableQueryLog(): void
    {
        $this->assertUsable();
        $this->loggingQueries = false;
    }

    public function logging(): bool
    {
        return $this->loggingQueries;
    }

    public function getQueryLog(): array
    {
        $this->assertUsable();
        return $this->queryLog;
    }

    public function flushQueryLog(): void
    {
        $this->assertUsable();
        $this->queryLog = [];
    }

    public function getRawQueryLog(): array
    {
        return $this->withConnection(fn () => array_map(fn (array $log) => [
            'raw_query' => $this->getQueryGrammar()->substituteBindingsIntoRawSql(
                $log['query'],
                array_map(fn ($value) => $this->__call('escape', [$value]), $this->prepareBindings($log['bindings']))
            ),
            'time' => $log['time'],
        ], $this->getQueryLog()));
    }

    public function recordsHaveBeenModified(bool $value = true): void
    {
        $this->callDriver('recordsHaveBeenModified', [$value]);
    }

    public function resetRecordsModified(): void
    {
        $this->callDriver('resetRecordsModified', []);
    }

    public function getQueryGrammar(): Grammar
    {
        return $this->getMetadata()->grammar;
    }

    public function setQueryGrammar(Grammar $grammar): static
    {
        $this->getMetadata()->grammar = $grammar;
        $this->updateMetadata();
        return $this;
    }

    public function getPostProcessor(): Processor
    {
        return $this->getMetadata()->processor;
    }

    public function setPostProcessor(Processor $processor): static
    {
        $this->getMetadata()->processor = $processor;
        $this->updateMetadata();
        return $this;
    }

    public function getName(): string
    {
        $this->assertUsable();
        return $this->name;
    }

    public function getConfig($option = null)
    {
        return Arr::get($this->getMetadata()->config, $option);
    }

    public function getDriverName()
    {
        return $this->getConfig('driver');
    }

    public function getDatabaseName(): string
    {
        return $this->getMetadata()->database;
    }

    public function setDatabaseName(string $database): static
    {
        $this->getMetadata()->database = $database;
        $this->updateMetadata();
        return $this;
    }

    public function getTablePrefix(): string
    {
        return $this->getMetadata()->prefix;
    }

    public function setTablePrefix(string $prefix): static
    {
        $metadata = $this->getMetadata();
        $metadata->prefix = $prefix;
        $metadata->grammar->setTablePrefix($prefix);
        $this->updateMetadata();
        return $this;
    }

    /** Driver extensions outside the minimal ConnectionInterface. */
    private function callDriver(string $method, array $arguments): mixed
    {
        return $this->runOperation(fn ($connection) => $connection->{$method}(...$arguments));
    }

    private function assertUsable(): void
    {
        if ($this->closed || $this->owner !== Coroutine::id()) {
            throw new LogicException('The database session is closed or belongs to another coroutine.');
        }
    }

    private function pinEscapedHandle(): void
    {
        $this->assertUsable();
        // Raw handles and unknown setters can change state which the framework
        // cannot reset reliably. Rebuild the driver before the next borrower.
        $this->opaqueState = true;
        if ($this->pins === 0) {
            $this->escaped = true;
        }
    }

    private function borrow(): ConnectionLease
    {
        $this->assertUsable();
        if ($this->lease === null) {
            $lease = $this->resolver->acquireLease($this->name);
            try {
                if ($this->metadata !== null) {
                    $lease->applyMetadata($this->metadata);
                }
                $database = $lease->getDatabaseConnection();
                if ($database instanceof DatabaseConnection) {
                    $database->setQueryObserver(function (string $query, array $bindings, ?float $time) {
                        if ($this->loggingQueries) {
                            $this->queryLog[] = compact('query', 'bindings', 'time');
                        }
                    });
                }
                $this->lease = $lease;
            } catch (Throwable $exception) {
                $lease->release(true);
                throw $exception;
            }
        }
        return $this->lease;
    }

    private function releaseIfIdle(): void
    {
        if ($this->lease !== null && ! $this->lease->isActive()) {
            // External wrapper release already ran cleanup. Preserve any callback
            // exception and leave a newer borrower's driver and pool slot untouched.
            $this->closed = true;
            $this->releaseLease();
            return;
        }
        if ($this->closed || $this->operations > 0 || $this->pins > 0 || $this->escaped || $this->lease === null) {
            return;
        }
        if (! ($this->releaseAfterUse ?? $this->resolver->isReleaseAfterUse($this->name))) {
            return;
        }
        $database = $this->lease->getDatabaseConnection();
        if ($database->transactionLevel() > 0) {
            return;
        }
        // Preserve existing sticky/session affinity, using the driver's actual state.
        // Drivers without an observable state contract fall back to scope lifetime.
        if (! $database instanceof DatabaseConnection || $database->pretending() || $database->logging()) {
            return;
        }
        if ($database->hasModifiedRecords() && $database->getConfig('sticky')) {
            return;
        }
        $this->releaseLease();
    }

    private function releaseLease(bool $invalidate = false): void
    {
        $lease = $this->lease;
        $this->lease = null;
        $invalidate = $invalidate || $this->opaqueState;
        $this->opaqueState = false;
        $lease?->release($invalidate);
    }

    private function updateMetadata(): void
    {
        if ($this->lease !== null && $this->metadata !== null) {
            $this->lease->applyMetadata($this->metadata);
        }
    }

    private function getMetadata(): ConnectionMetadata
    {
        $this->assertUsable();
        if ($this->metadata === null) {
            $config = $this->container->get(ConfigInterface::class);
            $key = sprintf('databases.%s', $this->name);
            if (! $config->has($key)) {
                throw new InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
            }
            $options = $config->get($key);
            $options['name'] = $this->name;
            $this->metadata = $this->container->get(ConnectionFactory::class)->makeMetadata($options);
            if ($this->metadata === null) {
                // A third-party driver has no IO-free metadata contract: use its
                // real settings and retain the lease instead of inventing a dialect.
                $this->escaped = true;
                $database = $this->borrow()->getDatabaseConnection();
                if (! $database instanceof DatabaseConnection) {
                    throw new LogicException('Lazy metadata requires a database Connection or a metadata resolver.');
                }
                $this->metadata = new ConnectionMetadata(
                    $database->getConfig(),
                    clone $database->getQueryGrammar(),
                    $database->getPostProcessor(),
                    $database->getDatabaseName(),
                    $database->getTablePrefix(),
                    get_class($database->query())
                );
            }
            $this->updateMetadata();
        }
        return $this->metadata;
    }
}
