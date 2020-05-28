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
namespace Hyperf\DbSQLiteDriver\Query\Processors;

use Hyperf\Database\Query\Processors\Processor;

class SQLiteProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param array $results
     */
    public function processColumnListing($results): array
    {
        return array_map(function ($result) {
            return ((object) $result)->name;
        }, $results);
    }
}
