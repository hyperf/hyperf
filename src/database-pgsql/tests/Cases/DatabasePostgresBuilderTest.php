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
namespace HyperfTest\Database\PgSQL\Cases;

use Hyperf\Database\Connection;
use Hyperf\Database\PgSQL\Schema\Grammars\PostgresGrammar;
use Hyperf\Database\PgSQL\Schema\PostgresBuilder;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabasePostgresBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testCreateDatabase()
    {
        $grammar = new PostgresGrammar();

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getConfig')->once()->with('charset')->andReturn('utf8');
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn($grammar);
        $connection->shouldReceive('statement')->once()->with(
            'create database "my_temporary_database" encoding "utf8"'
        )->andReturn(true);

        $builder = $this->getBuilder($connection);
        $this->assertEquals(true, $builder->createDatabase('my_temporary_database'));
    }

    public function testDropDatabaseIfExists()
    {
        $grammar = new PostgresGrammar();

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn($grammar);
        $connection->shouldReceive('statement')->once()->with(
            'drop database if exists "my_database_a"'
        )->andReturn(true);

        $builder = $this->getBuilder($connection);
        $this->assertEquals(true, $builder->dropDatabaseIfExists('my_database_a'));
    }

    protected function getBuilder($connection)
    {
        return new PostgresBuilder($connection);
    }
}
