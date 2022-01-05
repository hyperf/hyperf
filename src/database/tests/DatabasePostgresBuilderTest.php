<?php

namespace HyperfTest\Database;

use Hyperf\Database\Connection;
use Hyperf\Database\Schema\Grammars\PostgresGrammar;
use Hyperf\Database\Schema\PostgresBuilder;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DatabasePostgresBuilderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testCreateDatabase()
    {
        $grammar = new PostgresGrammar;

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
        $grammar = new PostgresGrammar;

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
