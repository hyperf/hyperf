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

namespace Hyperf\DbConnection\Traits;

use Closure;
use Generator;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;

/**
 * TODO: release connection except transaction.
 */
trait DbConnection
{
    public function table($table): Builder
    {
        return $this->connection->table($table);
    }

    public function raw($value): Expression
    {
        return $this->connection->table($value);
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        return $this->connection->selectOne($query, $bindings, $useReadPdo);
    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        return $this->connection->select($query, $bindings, $useReadPdo);
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        return $this->connection->cursor($query, $bindings, $useReadPdo);
    }

    public function insert($query, $bindings = []): bool
    {
        return $this->connection->insert($query, $bindings);
    }

    public function update($query, $bindings = []): int
    {
        return $this->connection->update($query, $bindings);
    }

    public function delete($query, $bindings = []): int
    {
        return $this->connection->delete($query, $bindings);
    }

    public function statement($query, $bindings = []): bool
    {
        return $this->connection->statement($query, $bindings);
    }

    public function affectingStatement($query, $bindings = []): int
    {
        return $this->connection->affectingStatement($query, $bindings);
    }

    public function unprepared($query): bool
    {
        return $this->connection->unprepared($query);
    }

    public function prepareBindings(array $bindings): array
    {
        return $this->connection->prepareBindings($bindings);
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        return $this->connection->transaction($callback, $attempts);
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function transactionLevel(): int
    {
        return $this->connection->transactionLevel();
    }

    public function pretend(Closure $callback): array
    {
        return $this->connection->pretend($callback);
    }
}
