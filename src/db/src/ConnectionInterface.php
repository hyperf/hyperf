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

interface ConnectionInterface
{
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
    public function rollBack(?int $toLevel = null): void;

    /**
     * Run an insert statement against the database.
     *
     * @return int last insert id
     */
    public function insert(string $query, array $bindings = []): int;

    /**
     * Run an execute statement against the database.
     *
     * @return int affected rows
     */
    public function execute(string $query, array $bindings = []): int;

    /**
     * Execute an SQL statement and return the number of affected rows.
     *
     * @return int affected rows
     */
    public function exec(string $sql): int;

    /**
     * Run a select statement against the database.
     */
    public function query(string $query, array $bindings = []): array;

    /**
     * Run a select statement and return a single result.
     */
    public function fetch(string $query, array $bindings = []);

    public function call(string $method, array $argument = []);

    public function run(Closure $closure);
}
