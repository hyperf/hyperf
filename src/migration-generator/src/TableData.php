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

use JetBrains\PhpStorm\ArrayShape;

final class TableData
{
    public function __construct(protected array $columns, protected array $indexes, protected string $comment)
    {
    }

    #[ArrayShape([
        [
            'column_name' => 'string',
            'extra' => 'string',
            'column_type' => 'string',
            'character_maximum_length' => 'int',
            'numeric_precision' => 'int',
            'numeric_scale' => 'int',
        ],
    ])]
    public function getColumns(): array
    {
        return $this->columns;
    }

    #[ArrayShape([
        [
            'table' => 'string',
            'key_name' => 'string',
            'non_unique' => 'string',
            'seq_in_index' => 'string',
            'column_name' => 'string',
            'index_type' => 'string',
        ],
    ])]
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
