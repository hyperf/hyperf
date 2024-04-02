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

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Connection;
use Hyperf\Database\SQLite\Schema\SQLiteBuilder;
use Hyperf\Support\Filesystem\Filesystem;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class DatabaseSQLiteBuilderTest extends TestCase
{
    public function testCreateDatabase()
    {
        $filesystem = m::mock(Filesystem::class);
        $filesystem->shouldReceive('put')
            ->once()
            ->with('my_temporary_database_a', '')
            ->andReturn(20);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with(Filesystem::class)
            ->andReturn($filesystem);
        ApplicationContext::setContainer($container);

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getSchemaGrammar')->once();

        $builder = new SQLiteBuilder($connection);

        $this->assertTrue($builder->createDatabase('my_temporary_database_a'));

        $filesystem->shouldReceive('put')
            ->once()
            ->with('my_temporary_database_b', '')
            ->andReturn(false);

        $this->assertFalse($builder->createDatabase('my_temporary_database_b'));
    }

    public function testDropDatabaseIfExists()
    {
        $filesystem = m::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $filesystem->shouldReceive('delete')
            ->once()
            ->with('my_temporary_database_b')
            ->andReturn(true);

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with(Filesystem::class)
            ->andReturn($filesystem);
        ApplicationContext::setContainer($container);

        $connection = m::mock(Connection::class);
        $connection->shouldReceive('getSchemaGrammar')->once();

        $builder = new SQLiteBuilder($connection);

        $this->assertTrue($builder->dropDatabaseIfExists('my_temporary_database_b'));

        $filesystem->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $this->assertTrue($builder->dropDatabaseIfExists('my_temporary_database_c'));

        $filesystem->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $filesystem->shouldReceive('delete')
            ->once()
            ->with('my_temporary_database_c')
            ->andReturn(false);

        $this->assertFalse($builder->dropDatabaseIfExists('my_temporary_database_c'));
    }
}
