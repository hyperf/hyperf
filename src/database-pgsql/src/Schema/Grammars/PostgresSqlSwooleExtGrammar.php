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
        return <<<'SQL'
        SELECT
            a.attname AS column_name,
            CASE
                   WHEN format_type(a.atttypid, a.atttypmod) LIKE 'numeric%' THEN
                       regexp_replace(format_type(a.atttypid, a.atttypmod), '^numeric', 'decimal')
                   WHEN format_type(a.atttypid, a.atttypmod) LIKE 'timestamp%' THEN 'datetime'
                   WHEN format_type(a.atttypid, a.atttypmod) = 'integer' THEN 'int'
                   WHEN format_type(a.atttypid, a.atttypmod) = 'real' THEN 'float'
                   WHEN format_type(a.atttypid, a.atttypmod) = 'double precision' THEN 'double'
                   ELSE format_type(a.atttypid, a.atttypmod)
                   END                               AS data_type,
            col_description(a.attrelid, a.attnum) AS column_comment,
            CASE
                WHEN i.indisprimary THEN 'PRI'
                WHEN i.indisunique THEN 'UNI'
                ELSE NULL
            END AS column_key,
            CASE
                -- Detect SERIAL type (via default value using nextval)
                WHEN d.adbin IS NOT NULL AND pg_get_expr(d.adbin, d.adrelid) LIKE 'nextval(%' THEN 'auto_increment'
                -- Detect GENERATED AS IDENTITY (a = by default, d = always) pgsql10+
                WHEN a.attidentity IN ('a', 'd') THEN 'auto_increment'
                ELSE NULL
            END AS extra,
            CASE WHEN a.attnotnull THEN 'NO' ELSE 'YES' END AS is_nullable,
            pg_get_expr(d.adbin, d.adrelid) AS column_default
        FROM pg_attribute a
        JOIN pg_class c ON a.attrelid = c.oid
        JOIN pg_namespace n ON c.relnamespace = n.oid
        LEFT JOIN
            pg_index i ON i.indrelid = c.oid AND a.attnum = ANY(i.indkey)
        LEFT JOIN pg_attrdef d ON d.adrelid = c.oid AND d.adnum = a.attnum
        WHERE
            CAST($1 AS text) IS NOT NULL AND -- ignore table_catalog
            n.nspname = $2 AND
            c.relname = $3 AND
            a.attnum > 0 AND NOT a.attisdropped
        ORDER BY a.attnum
        SQL;
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumns(): string
    {
        return 'select table_schema,table_name,column_name,ordinal_position,column_default,is_nullable,data_type from information_schema.columns where table_schema = $1 order by ORDINAL_POSITION';
    }
}
