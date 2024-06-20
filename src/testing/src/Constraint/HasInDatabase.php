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

namespace Hyperf\Testing\Constraint;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Builder;
use PHPUnit\Framework\Constraint\Constraint;

class HasInDatabase extends Constraint
{
    protected ConnectionInterface $connection;

    protected string $table;

    public function __construct(ConnectionInterface $connection, string $table)
    {
        $this->table = $table;
        $this->connection = $connection;
    }

    public function matches($data): bool
    {
        return $this->query($data)->count() > 0;
    }

    public function failureDescription($data): string
    {
        return sprintf(
            'a row in the table [%s] matches the attributes %s',
            $this->table,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function toString(): string
    {
        return sprintf('There is a record of table %s with the specified data', $this->table);
    }

    private function query(array $data): Builder
    {
        /** @var Builder $query */
        $query = $this->connection->table($this->table);

        foreach ($data as $index => $value) {
            if (is_array($value)) {
                [$column, $operator, $expected] = $value;
                $query->where($column, $operator, $expected);
                continue;
            }

            $query->where($index, $value);
        }

        return $query;
    }
}
