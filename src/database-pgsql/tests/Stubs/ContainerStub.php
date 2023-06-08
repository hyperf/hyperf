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
namespace HyperfTest\Database\PgSQL\Stubs;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\PgSQL\Connectors\PostgresSqlSwooleExtConnector;
use Hyperf\Database\PgSQL\PostgreSqlSwooleExtConnection;
use Mockery;
use Psr\Container\ContainerInterface;

class ContainerStub
{
    public static function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with('db.connector.pgsql-swoole')->andReturn(new PostgresSqlSwooleExtConnector());
        $connector = new ConnectionFactory($container);

        Connection::resolverFor('pgsql-swoole', static function ($connection, $database, $prefix, $config) {
            return new PostgreSqlSwooleExtConnection($connection, $database, $prefix, $config);
        });

        $connection = $connector->make([
            'driver' => 'pgsql-swoole',
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'postgres',
            'username' => 'postgres',
            'password' => 'postgres',
        ]);

        $resolver = new ConnectionResolver(['default' => $connection]);

        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);

        ApplicationContext::setContainer($container);

        return $container;
    }
}
