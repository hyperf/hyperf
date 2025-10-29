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

namespace Hyperf\Database\PgSQL\Query\Processors;

use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Processors\Processor;

class PostgresProcessor extends Processor
{
    /**
     * Process an "insert get ID" query.
     *
     * @param string $sql
     * @param array $values
     * @param null|string $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $connection = $query->getConnection();

        $connection->recordsHaveBeenModified();

        $result = $connection->selectFromWriteConnection($sql, $values)[0];

        $sequence = $sequence ?: 'id';

        $id = is_object($result) ? $result->{$sequence} : $result[$sequence];

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process the results of a column listing query.
     *
     * @param array $results
     */
    public function processColumnListing($results): array
    {
        return array_map(function ($result) {
            return ((object) $result)->column_name;
        }, $results);
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
     * Process the results of an indexes query.
     */
    public function processIndexes(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => strtolower($result->name),
                'columns' => explode(',', $result->columns),
                'type' => strtolower($result->type),
                'unique' => (bool) $result->unique,
                'primary' => (bool) $result->primary,
            ];
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
                'on_update' => match (strtolower($result->on_update)) {
                    'a' => 'no action',
                    'r' => 'restrict',
                    'c' => 'cascade',
                    'n' => 'set null',
                    'd' => 'set default',
                    default => null,
                },
                'on_delete' => match (strtolower($result->on_delete)) {
                    'a' => 'no action',
                    'r' => 'restrict',
                    'c' => 'cascade',
                    'n' => 'set null',
                    'd' => 'set default',
                    default => null,
                },
            ];
        }, $results);
    }
}
