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

use Hyperf\Database\Query\Builder;
use Hyperf\Database\Schema\Column;

class Processor
{
    /**
     * Process the results of a "select" query.
     *
     * @param array $results
     * @return array
     */
    public function processSelect(Builder $query, $results)
    {
        return $results;
    }

    /**
     * Process an  "insert get ID" query.
     *
     * @param string $sql
     * @param array $values
     * @param string $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process the results of a tables query.
     */
    public function processTables(array $results): array
    {
        return array_map(static function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'schema' => $result->schema ?? null, // PostgreSQL and SQL Server
                'size' => isset($result->size) ? (int) $result->size : null,
                'comment' => $result->comment ?? null, // MySQL and PostgreSQL
                'collation' => $result->collation ?? null, // MySQL only
                'engine' => $result->engine ?? null, // MySQL only
            ];
        }, $results);
    }

    /**
     * Process the results of a column listing query.
     */
    public function processColumnListing(array $results): array
    {
        return $results;
    }

    /**
     * @return Column[]
     */
    public function processColumns(array $results): array
    {
        $columns = [];
        foreach ($results as $item) {
            $columns[] = new Column(...array_values($item));
        }

        return $columns;
    }

    /**
     * Process the results of an indexes query.
     */
    public function processIndexes(array $results): array
    {
        return $results;
    }

    /**
     * Process the results of a views query.
     */
    public function processViews(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'schema' => $result->schema ?? null, // PostgreSQL and SQL Server
                'definition' => $result->definition,
            ];
        }, $results);
    }

    /**
     * Process the results of a foreign keys query.
     */
    public function processForeignKeys(array $results): array
    {
        return $results;
    }
}
