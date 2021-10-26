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
namespace Hyperf\Database;

use Closure;
use Generator;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;

interface ConnectionInterface
{
    /**
     * Begin a fluent query against a database table.
     * @param Expression|string $table
     */
    public function table($table): Builder;

    /**
     * Get a new raw query expression.
     * @param mixed $value
     */
    public function raw($value): Expression;

    /**
     * Run a select statement and return a single result.
     */
    public function selectOne(string $query, array $bindings = [], bool $useReadPdo = true);

    /**
     * Run a select statement against the database.
     */
    public function select(string $query, array $bindings = [], bool $useReadPdo = true): array;

    /**
     * Run a select statement against the database and returns a generator.
     */
    public function cursor(string $query, array $bindings = [], bool $useReadPdo = true): Generator;

    /**
     * Run an insert statement against the database.
     */
    public function insert(string $query, array $bindings = []): bool;

    /**
     * Run an update statement against the database.
     */
    public function update(string $query, array $bindings = []): int;

    /**
     * Run a delete statement against the database.
     */
    public function delete(string $query, array $bindings = []): int;

    /**
     * Execute an SQL statement and return the boolean result.
     */
    public function statement(string $query, array $bindings = []): bool;

    /**
     * Run an SQL statement and get the number of rows affected.
     */
    public function affectingStatement(string $query, array $bindings = []): int;

    /**
     * Run a raw, unprepared query against the PDO connection.
     */
    public function unprepared(string $query): bool;

    /**
     * Prepare the query bindings for execution.
     */
    public function prepareBindings(array $bindings): array;

    /**
     * Execute a Closure within a transaction.
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, int $attempts = 1);

    /**
     * Start a new database transaction.
     */
    public function beginTransaction(): void;

    /**
     * Commit the active database transaction.
     */
    public function commit(): void;

    /**
     * Rollback the active database transaction.
     */
    public function rollBack(): void;

    /**
     * Get the number of active transactions.
     */
    public function transactionLevel(): int;

    /**
     * Execute the given callback in "dry run" mode.
     */
    public function pretend(Closure $callback): array;
}
