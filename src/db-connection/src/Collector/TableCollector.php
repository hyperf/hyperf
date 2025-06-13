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

namespace Hyperf\DbConnection\Collector;

use Hyperf\Database\Schema\Column;
use InvalidArgumentException;

class TableCollector
{
    /**
     * @var array<string, array<string, Column>>
     */
    protected array $data = [];

    /**
     * @param Column[] $columns
     */
    public function set(string $pool, string $table, array $columns): void
    {
        $this->validateColumns($columns);
        $this->data[$pool][$table] = $columns;
    }

    public function add(string $pool, Column $column): void
    {
        $this->data[$pool][$column->getTable()][$column->getName()] = $column;
    }

    public function get(string $pool, ?string $table = null): array
    {
        if ($table === null) {
            return $this->data[$pool] ?? [];
        }

        return $this->data[$pool][$table] ?? [];
    }

    public function has(string $pool, ?string $table = null): bool
    {
        return ! empty($this->get($pool, $table));
    }

    public function getDefaultValue(string $connectName, string $table): array
    {
        $columns = $this->get($connectName, $table);
        $list = [];
        foreach ($columns as $column) {
            $list[$column->getName()] = $column->getDefault();
        }
        return $list;
    }

    /**
     * @throws InvalidArgumentException When $columns is not equal to Column[]
     */
    protected function validateColumns(array $columns): void
    {
        foreach ($columns as $column) {
            if (! $column instanceof Column) {
                throw new InvalidArgumentException('Invalid columns.');
            }
        }
    }
}
