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

use Closure;
use Hyperf\Collection\Collection;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Migrations\DatabaseMigrationRepository;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Schema\Builder as SchemaBuilder;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseMigrationRepositoryTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetRanMigrationsListMigrationsByPackage()
    {
        $repo = $this->getRepository();
        $query = Mockery::mock(Builder::class);
        $connectionMock = Mockery::mock(Connection::class);
        $repo->getConnectionResolver()->shouldReceive('connection')->with(null)->andReturn($connectionMock);
        $repo->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);
        $query->shouldReceive('orderBy')->once()->with('batch', 'asc')->andReturn($query);
        $query->shouldReceive('orderBy')->once()->with('migration', 'asc')->andReturn($query);
        $query->shouldReceive('pluck')->once()->with('migration')->andReturn(new Collection(['bar']));
        $query->shouldReceive('useWritePdo')->once()->andReturn($query);

        $this->assertEquals(['bar'], $repo->getRan());
    }

    public function testGetLastMigrationsGetsAllMigrationsWithTheLatestBatchNumber()
    {
        $repo = $this->getMockBuilder(DatabaseMigrationRepository::class)->onlyMethods(['getLastBatchNumber'])->setConstructorArgs([
            $resolver = Mockery::mock(ConnectionResolverInterface::class), 'migrations',
        ])->getMock();
        $repo->expects($this->once())->method('getLastBatchNumber')->will($this->returnValue(1));
        $query = Mockery::mock(Builder::class);
        $connectionMock = Mockery::mock(Connection::class);
        $repo->getConnectionResolver()->shouldReceive('connection')->with(null)->andReturn($connectionMock);
        $repo->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);
        $query->shouldReceive('where')->once()->with('batch', 1)->andReturn($query);
        $query->shouldReceive('orderBy')->once()->with('migration', 'desc')->andReturn($query);
        $query->shouldReceive('get')->once()->andReturn(new Collection(['foo']));
        $query->shouldReceive('useWritePdo')->once()->andReturn($query);

        $this->assertEquals(['foo'], $repo->getLast());
    }

    public function testLogMethodInsertsRecordIntoMigrationTable()
    {
        $repo = $this->getRepository();
        $query = Mockery::mock(Builder::class);
        $connectionMock = Mockery::mock(Connection::class);
        $repo->getConnectionResolver()->shouldReceive('connection')->with(null)->andReturn($connectionMock);
        $repo->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);
        $query->shouldReceive('insert')->once()->with(['migration' => 'bar', 'batch' => 1]);
        $query->shouldReceive('useWritePdo')->once()->andReturn($query);

        $repo->log('bar', 1);
    }

    public function testDeleteMethodRemovesAMigrationFromTheTable()
    {
        $repo = $this->getRepository();
        $query = Mockery::mock(Builder::class);
        $connectionMock = Mockery::mock(Connection::class);
        $repo->getConnectionResolver()->shouldReceive('connection')->with(null)->andReturn($connectionMock);
        $repo->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);
        $query->shouldReceive('where')->once()->with('migration', 'foo')->andReturn($query);
        $query->shouldReceive('delete')->once();
        $query->shouldReceive('useWritePdo')->once()->andReturn($query);
        $migration = (object) ['migration' => 'foo'];

        $repo->delete($migration);
    }

    public function testGetNextBatchNumberReturnsLastBatchNumberPlusOne()
    {
        $repo = $this->getMockBuilder(DatabaseMigrationRepository::class)->onlyMethods(['getLastBatchNumber'])->setConstructorArgs([
            Mockery::mock(ConnectionResolverInterface::class), 'migrations',
        ])->getMock();
        $repo->expects($this->once())->method('getLastBatchNumber')->will($this->returnValue(1));

        $this->assertEquals(2, $repo->getNextBatchNumber());
    }

    public function testGetLastBatchNumberReturnsMaxBatch()
    {
        $repo = $this->getRepository();
        $query = Mockery::mock(Builder::class);
        $connectionMock = Mockery::mock(Connection::class);
        $repo->getConnectionResolver()->shouldReceive('connection')->with(null)->andReturn($connectionMock);
        $repo->getConnection()->shouldReceive('table')->once()->with('migrations')->andReturn($query);
        $query->shouldReceive('max')->once()->andReturn(1);
        $query->shouldReceive('useWritePdo')->once()->andReturn($query);

        $this->assertEquals(1, $repo->getLastBatchNumber());
    }

    public function testCreateRepositoryCreatesProperDatabaseTable()
    {
        $repo = $this->getRepository();
        $schema = Mockery::mock(SchemaBuilder::class);
        $connectionMock = Mockery::mock(Connection::class);
        $repo->getConnectionResolver()->shouldReceive('connection')->with(null)->andReturn($connectionMock);
        $repo->getConnection()->shouldReceive('getSchemaBuilder')->once()->andReturn($schema);
        $schema->shouldReceive('create')->once()->with('migrations', Mockery::type(Closure::class));

        $repo->createRepository();
    }

    protected function getRepository()
    {
        return new DatabaseMigrationRepository(Mockery::mock(ConnectionResolverInterface::class), 'migrations');
    }
}
