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
namespace Hyperf\MigrationGenerator;

final class TableData
{
    public function __construct(protected array $columns, protected array $indexes, protected string $comment)
    {
    }

    /**
     * @return [[
     *     'extra' => 'auto_increment',
     *     'column_type' => '',
     *     'character_maximum_length' => 4,
     *     'numeric_precision' => 0,
     *     'numeric_scale' => 0,
     * ]]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return [[
     *     'table' => '',
     *     'key_name' => '',
     *     'non_unique' => '',
     *     'seq_in_index' => '',
     *     'column_name' => '',
     *     'index_type' => 'BTREE',
     * ]]
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
