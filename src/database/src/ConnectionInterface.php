<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database;

use Closure;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Generator;

interface ConnectionInterface
{
    /**
     * Begin a fluent query against a database table.
     *
     * @param  string $table
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table): Builder;

    /**
     * Get a new raw query expression.
     *
     * @param  mixed $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value): Expression;

    /**
     * Run a select statement and return a single result.
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true);

    /**
     * Run a select statement against the database.
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true): array;

    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param  string $query
     * @param  array $bindings
     * @param  bool $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true): Generator;

    /**
     * Run an insert statement against the database.
     *
     * @param  string $query
     * @param  array $bindings
     * @return bool
     */
    public function insert($query, $bindings = []): bool;

    /**
     * Run an update statement against the database.
     *
     * @param  string $query
     * @param  array $bindings
     * @return int
     */
    public function update($query, $bindings = []): int;

    /**
     * Run a delete statement against the database.
     *
     * @param  string $query
     * @param  array $bindings
     * @return int
     */
    public function delete($query, $bindings = []): int;

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string $query
     * @param  array $bindings
     * @return bool
     */
    public function statement($query, $bindings = []): bool;

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param  string $query
     * @param  array $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = []): int;

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param  string $query
     * @return bool
     */
    public function unprepared($query): bool;

    /**
     * Prepare the query bindings for execution.
     *
     * @param  array $bindings
     * @return array
     */
    public function prepareBindings(array $bindings): array;

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure $callback
     * @param  int $attempts
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1);

    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack(): void;

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel(): int;

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param  \Closure $callback
     * @return array
     */
    public function pretend(Closure $callback): array;
}
