<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DB;

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
    public function rollBack(): void;

    /**
     * Run an insert statement against the database.
     *
     * @return int|string last insert id
     */
    public function insert(string $query, array $bindings = []);

    /**
     * Run an update statement against the database.
     *
     * @return int rows affected
     */
    public function execute(string $query, array $bindings = []): int;

    /**
     * Run a select statement against the database.
     */
    public function query(string $query, array $bindings = []): array;

    /**
     * Run a select statement and return a single result.
     */
    public function fetch(string $query, array $bindings = []): array;
}
