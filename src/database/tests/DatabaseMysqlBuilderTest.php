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

namespace HyperfTest\Database;

use Hyperf\Database\Connection;
use Hyperf\Database\Schema\Grammars\MySqlGrammar;
use Hyperf\Database\Schema\MySqlBuilder;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseMysqlBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testCreateDatabase()
    {
        $grammar = new MySqlGrammar();

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getConfig')->once()->with('charset')->andReturn('utf8mb4');
        $connection->shouldReceive('getConfig')->once()->with('collation')->andReturn('utf8mb4_unicode_ci');
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn($grammar);
        $connection->shouldReceive('statement')->once()->andReturnUsing(function ($sql) {
            $this->assertSame('create database `my_temporary_database` default character set `utf8mb4` default collate `utf8mb4_unicode_ci`', $sql);
            return true;
        });

        $builder = new MySqlBuilder($connection);
        $builder->createDatabase('my_temporary_database');
    }

    public function testDropDatabaseIfExists()
    {
        $grammar = new MySqlGrammar();

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn($grammar);
        $connection->shouldReceive('statement')->once()->andReturnUsing(function ($sql) {
            $this->assertSame('drop database if exists `my_database_a`', $sql);
            return true;
        });

        $builder = new MySqlBuilder($connection);

        $builder->dropDatabaseIfExists('my_database_a');
    }
}
