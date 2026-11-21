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

namespace HyperfTest\Database\Sqlsrv\Cases;

use Hyperf\Database\Connection;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Sqlsrv\Query\Processors\SqlServerProcessor;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SqlServerProcessorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    #[DataProvider('odbcInsertIdRows')]
    public function testProcessInsertGetIdForOdbcReturnsInteger(array|object $row)
    {
        $sql = 'insert into [users] ([email]) values (?)';
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')->once()->with($sql, ['foo'])->andReturnTrue();
        $connection->shouldReceive('getConfig')->with('odbc')->andReturnTrue();
        $connection->shouldReceive('selectFromWriteConnection')->once()->andReturn([$row]);
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getConnection')->andReturn($connection);

        $this->assertSame(42, (new SqlServerProcessor())->processInsertGetId($builder, $sql, ['foo'], 'id'));
    }

    public static function odbcInsertIdRows(): array
    {
        return [
            'object' => [(object) ['insertid' => '42']],
            'array' => [['insertid' => '42']],
        ];
    }
}
