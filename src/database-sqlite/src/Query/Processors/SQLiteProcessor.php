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

namespace Hyperf\Database\SQLite\Query\Processors;

use Hyperf\Database\Query\Processors\Processor;
use Hyperf\Database\Schema\Column;

class SQLiteProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     */
    public function processColumnListing(array $results): array
    {
        return array_map(function ($result) {
            return ((object) $result)->name;
        }, $results);
    }

    /**
     * Process the results of a columns query.
     */
    public function processColumns(array $results): array
    {
        return array_map(function ($result) {
            return new Column(
                'main',
                $result['table_name'],
                $result['column_name'],
                $result['cid'] + 1,
                $result['default'],
                (bool) $result['nullable'],
                strtok(strtolower($result['type']), '(') ?: '',
                ''
            );
        }, $results);
    }

    /**
     * Process the results of an indexes query.
     */
    public function processIndexes(array $results): array
    {
        $primaryCount = 0;

        $indexes = array_map(function ($result) use (&$primaryCount) {
            $result = (object) $result;

            if ($isPrimary = (bool) $result->primary) {
                ++$primaryCount;
            }

            return [
                'name' => strtolower($result->name),
                'columns' => explode(',', $result->columns),
                'type' => null,
                'unique' => (bool) $result->unique,
                'primary' => $isPrimary,
            ];
        }, $results);

        if ($primaryCount > 1) {
            $indexes = array_filter($indexes, fn ($index) => $index['name'] !== 'primary');
        }

        return $indexes;
    }

    /**
     * Process the results of a foreign keys query.
     */
    public function processForeignKeys(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => null,
                'columns' => explode(',', $result->columns),
                'foreign_schema' => null,
                'foreign_table' => $result->foreign_table,
                'foreign_columns' => explode(',', $result->foreign_columns),
                'on_update' => strtolower($result->on_update),
                'on_delete' => strtolower($result->on_delete),
            ];
        }, $results);
    }
}
