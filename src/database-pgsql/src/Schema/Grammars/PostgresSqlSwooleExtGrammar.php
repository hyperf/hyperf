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

namespace Hyperf\Database\PgSQL\Schema\Grammars;

class PostgresSqlSwooleExtGrammar extends PostgresGrammar
{
    /**
     * Compile the query to determine if a table exists.
     */
    public function compileTableExists(): string
    {
        return "select * from information_schema.tables where table_schema = $1 and table_name = $2 and table_type = 'BASE TABLE'";
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumnListing(): string
    {
        return 'select column_name as column_name, data_type as data_type from information_schema.columns where table_catalog = $1 and table_schema = $2 and table_name = $3';
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumns(): string
    {
        return 'select table_schema,table_name,column_name,ordinal_position,column_default,is_nullable,data_type from information_schema.columns where table_schema = $1 order by ORDINAL_POSITION';
    }
}
