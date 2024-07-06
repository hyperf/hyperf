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

namespace HyperfTest\Database\SQLite;

use Hyperf\Database\Schema\Column;
use Hyperf\Database\SQLite\Query\Processors\SQLiteProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSQLiteProcessorTest extends TestCase
{
    public function testProcessColumnListing()
    {
        $processor = new SQLiteProcessor();

        $listing = [
            ['name' => 'id', 'type' => 'INTEGER', 'notnull' => '0', 'default' => '', 'pk' => '1'],
            ['name' => 'name', 'type' => 'varchar', 'notnull' => '1', 'default' => 'foo', 'pk' => '0'],
            ['name' => 'is_active', 'type' => 'tinyint(1)', 'notnull' => '0', 'default' => '1', 'pk' => '0'],
        ];
        $expected = ['id', 'name', 'is_active'];

        $this->assertSame($expected, $processor->processColumnListing($listing));
    }

    public function testProcessColumns()
    {
        $processor = new SQLiteProcessor();

        $listing = [
            ['table_name' => 'foo', 'column_name' => 'id', 'type' => 'INTEGER', 'nullable' => '0', 'default' => '', 'primary' => '1', 'cid' => 0],
            ['table_name' => 'foo', 'column_name' => 'name', 'type' => 'varchar', 'nullable' => '1', 'default' => 'foo', 'primary' => '0', 'cid' => 1],
            ['table_name' => 'foo', 'column_name' => 'is_active', 'type' => 'tinyint(1)', 'nullable' => '0', 'default' => '1', 'primary' => '0', 'cid' => 2],
        ];

        $this->assertSame(3, count($columns = $processor->processColumns($listing)));
        $this->assertInstanceOf(Column::class, $column = $columns[0]);
        $this->assertSame('foo', $column->getTable());
        $this->assertSame('id', $column->getName());
        $this->assertSame(1, $column->getPosition());
        $this->assertSame('integer', $column->getType());
        $this->assertEmpty($column->getDefault());
        $this->assertFalse($column->isNullable());
    }
}
