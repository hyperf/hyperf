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

namespace Hyperf\DbConnection;

use Closure;
use Generator;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Hyperf\Pool\ConnectionInterface as PoolConnInterface;

class Connection implements ConnectionInterface, PoolConnInterface
{
    public function table($table): Builder
    {
        // TODO: Implement table() method.
    }

    public function raw($value): Expression
    {
        // TODO: Implement raw() method.
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        // TODO: Implement selectOne() method.
    }

    public function select($query, $bindings = [], $useReadPdo = true): array
    {
        // TODO: Implement select() method.
    }

    public function cursor($query, $bindings = [], $useReadPdo = true): Generator
    {
        // TODO: Implement cursor() method.
    }

    public function insert($query, $bindings = []): bool
    {
        // TODO: Implement insert() method.
    }

    public function update($query, $bindings = []): int
    {
        // TODO: Implement update() method.
    }

    public function delete($query, $bindings = []): int
    {
        // TODO: Implement delete() method.
    }

    public function statement($query, $bindings = []): bool
    {
        // TODO: Implement statement() method.
    }

    public function affectingStatement($query, $bindings = []): int
    {
        // TODO: Implement affectingStatement() method.
    }

    public function unprepared($query): bool
    {
        // TODO: Implement unprepared() method.
    }

    public function prepareBindings(array $bindings): array
    {
        // TODO: Implement prepareBindings() method.
    }

    public function transaction(Closure $callback, $attempts = 1)
    {
        // TODO: Implement transaction() method.
    }

    public function beginTransaction(): void
    {
        // TODO: Implement beginTransaction() method.
    }

    public function commit(): void
    {
        // TODO: Implement commit() method.
    }

    public function rollBack(): void
    {
        // TODO: Implement rollBack() method.
    }

    public function transactionLevel(): int
    {
        // TODO: Implement transactionLevel() method.
    }

    public function pretend(Closure $callback): array
    {
        // TODO: Implement pretend() method.
    }
}
