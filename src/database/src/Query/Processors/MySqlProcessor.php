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

namespace Hyperf\Database\Query\Processors;

class MySqlProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param array $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return ((object) $result)->column_name;
        }, $results);
    }

    /**
     * Process the results of a column type listing query.
     *
     * @param array $results
     * @return array
     */
    public function processListing($results)
    {
        return array_map(function ($result) {
            return (array) $result;
        }, $results);
    }
}
