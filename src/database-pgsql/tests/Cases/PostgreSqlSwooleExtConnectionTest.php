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
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\PgSQL\Connectors\PostgresSqlSwooleExtConnector;
use Hyperf\Database\PgSQL\PostgreSqlSwooleExtConnection;
use Hyperf\Database\Query\Builder;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class PostgreSqlSwooleExtConnectionTest extends TestCase
{
    protected ConnectionFactory $connectionFactory;

    public function setUp(): void
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with('db.connector.pgsql-swoole')->andReturn(new PostgresSqlSwooleExtConnector());

        $this->connectionFactory = new ConnectionFactory($container);

        Connection::resolverFor('pgsql-swoole', static function ($connection, $database, $prefix, $config) {
            return new PostgreSqlSwooleExtConnection($connection, $database, $prefix, $config);
        });
    }

    public function testSelectMethodDuplicateKeyValueException()
    {
        if (SWOOLE_MAJOR_VERSION < 5) {
            $this->markTestSkipped('PostgreSql requires Swoole version >= 5.0.0');
        }

        $connection = $this->connectionFactory->make([
            'driver' => 'pgsql-swoole',
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'postgres',
            'username' => 'postgres',
            'password' => 'postgres',
        ]);

        $builder = new Builder($connection);

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('ERROR:  duplicate key value violates unique constraint "users_email"');

        $id = $builder->from('users')->insertGetId(['email' => 'test@hyperf.io', 'name' => 'hyperf'], 'id');
        $id2 = $builder->from('users')->insertGetId(['email' => 'test@hyperf.io', 'name' => 'hyperf'], 'id');

        // Never here
        $this->assertIsNumeric($id);
        $this->assertIsNumeric($id2);
    }

    public function testAffectingStatementWithWrongSql()
    {
        if (SWOOLE_MAJOR_VERSION < 5) {
            $this->markTestSkipped('PostgreSql requires Swoole version >= 5.0.0');
        }

        $connection = $this->connectionFactory->make([
            'driver' => 'pgsql-swoole',
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'postgres',
            'username' => 'postgres',
            'password' => 'postgres',
        ]);

        $this->expectException(QueryException::class);

        $connection->affectingStatement('UPDATE xx SET x = 1 WHERE id = 1');
    }
}
