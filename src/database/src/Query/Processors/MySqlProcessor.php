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

namespace Hyperf\Database\Query\Processors;

use Hyperf\Database\Schema\Column;

class MySqlProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     */
    public function processColumnListing(array $results): array
    {
        return array_map(function ($result) {
            return ((object) $result)->column_name;
        }, $results);
    }

    public function processColumns(array $results): array
    {
        $columns = [];
        foreach ($results as $i => $value) {
            $item = array_change_key_case((array) $value, CASE_LOWER);
            $columns[$i] = new Column(
                $item['table_schema'],
                $item['table_name'],
                $item['column_name'],
                $item['ordinal_position'],
                $item['column_default'],
                $item['is_nullable'] === 'YES',
                $item['data_type'],
                $item['column_comment']
            );
        }

        return $columns;
    }

    /**
     * Process the results of a column type listing query.
     */
    public function processListing(array $results): array
    {
        return array_map(function ($result) {
            return (array) $result;
        }, $results);
    }

    /**
     * Process the results of a foreign keys query.
     */
    public function processForeignKeys(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'columns' => explode(',', $result->columns),
                'foreign_schema' => $result->foreign_schema,
                'foreign_table' => $result->foreign_table,
                'foreign_columns' => explode(',', $result->foreign_columns),
                'on_update' => strtolower($result->on_update),
                'on_delete' => strtolower($result->on_delete),
            ];
        }, $results);
    }
}
