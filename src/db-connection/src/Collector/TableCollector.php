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

namespace Hyperf\DbConnection\Collector;

class TableCollector
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param Column[] $columns
     */
    public function set(string $connectName, string $table, array $columns)
    {
        $this->data[$connectName][$table] = $columns;
    }

    public function get(string $connectName, string $table)
    {
        return $this->data[$connectName][$table] ?? [];
    }

    public function getDabatase(string $connectName)
    {
        return $this->data[$connectName] ?? [];
    }

    public function getDefaultValue(string $connectName, string $table): array
    {
        $tablseData = $this->get($connectName, $table);
        $list = [];
        foreach ($tablseData as $column) {
            $list[$column->getName()] = $column->getDefault();
        }
        return $list;
    }
}
