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
namespace HyperfTest\DbConnection\Stubs;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\DbConnection\Frequency;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Hyperf\Framework\Logger\StdoutLogger;
use Mockery;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LogLevel;

class ContainerStub
{
    public static function mockContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $factory = new PoolFactory($container);
        $container->shouldReceive('get')->with(PoolFactory::class)->andReturn($factory);

        $resolver = new ConnectionResolver($container);
        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);

        $config = new Config([
            StdoutLoggerInterface::class => [
                'log_level' => [
                    LogLevel::ALERT,
                    LogLevel::CRITICAL,
                    LogLevel::DEBUG,
                    LogLevel::EMERGENCY,
                    LogLevel::ERROR,
                    LogLevel::INFO,
                    LogLevel::NOTICE,
                    LogLevel::WARNING,
                ],
            ],
            'databases' => [
                'default' => [
                    'driver' => 'mysql',
                    'host' => '127.0.0.1',
                    'database' => 'hyperf',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60.0,
                    ],
                ],
            ],
        ]);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        $logger = new StdoutLogger($config);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn($logger);

        $connectionFactory = new ConnectionFactory($container);
        $container->shouldReceive('get')->with(ConnectionFactory::class)->andReturn($connectionFactory);

        $eventDispatcher = new EventDispatcher(new ListenerProvider(), $logger);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($eventDispatcher);

        $container->shouldReceive('get')->with('db.connector.mysql')->andReturn(new MySqlConnector());
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('make')->with(Frequency::class)->andReturn(new Frequency());

        return $container;
    }

    public static function mockReadWriteContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $factory = new PoolFactory($container);
        $container->shouldReceive('get')->with(PoolFactory::class)->andReturn($factory);

        $resolver = new ConnectionResolver($container);
        $container->shouldReceive('get')->with(ConnectionResolverInterface::class)->andReturn($resolver);

        $config = new Config([
            'databases' => [
                'default' => [
                    'driver' => 'mysql',
                    'read' => [
                        'host' => ['192.168.1.1'],
                    ],
                    'write' => [
                        'host' => ['192.168.1.2'],
                    ],
                    'sticky' => true,
                    'database' => 'hyperf',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => 60.0,
                    ],
                ],
            ],
        ]);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive(Mockery::any())->andReturn(null);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn($logger);

        $connectionFactory = new ConnectionFactory($container);
        $container->shouldReceive('get')->with(ConnectionFactory::class)->andReturn($connectionFactory);

        $eventDispatcher = new EventDispatcher(new ListenerProvider(), $logger);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($eventDispatcher);

        $container->shouldReceive('get')->with('db.connector.mysql')->andReturn(new MySqlConnectorStub());
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('make')->with(Frequency::class)->andReturn(new Frequency());

        return $container;
    }
}
