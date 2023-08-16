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
namespace HyperfTest\DB\Cases;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DB\DB;
use Hyperf\DB\Frequency;
use Hyperf\DB\PgSQL\PgSQLPool;
use Hyperf\DB\Pool\MySQLPool;
use Hyperf\DB\Pool\PDOPool;
use Hyperf\DB\Pool\PoolFactory;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends TestCase
{
    protected $driver = 'pdo';

    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('db.connection.default', null);
    }

    protected function getContainer($options = [])
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'db' => [
                'default' => [
                    'driver' => $this->driver,
                    'host' => '127.0.0.1',
                    'password' => '',
                    'database' => 'hyperf',
                    'pool' => [
                        'max_connections' => 20,
                    ],
                    'options' => $options,
                ],
                'pdo' => [
                    'driver' => 'pdo',
                    'host' => '127.0.0.1',
                    'password' => '',
                    'database' => 'hyperf',
                    'pool' => [
                        'max_connections' => 20,
                    ],
                    'options' => $options,
                ],
                'pgsql' => [
                    'driver' => 'pgsql',
                    'host' => '127.0.0.1',
                    'port' => 5432,
                    'database' => 'postgres',
                    'username' => 'postgres',
                    'password' => 'postgres',
                ],
            ],
        ]));
        $container->shouldReceive('make')->with(PDOPool::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new PDOPool(...array_values($args));
        });
        $container->shouldReceive('make')->with(MySQLPool::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new MySQLPool(...array_values($args));
        });
        $container->shouldReceive('make')->with(PgSQLPool::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new PgSQLPool(...array_values($args));
        });
        $container->shouldReceive('make')->with(Frequency::class, Mockery::any())->andReturn(new Frequency());
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Channel(...array_values($args));
        });
        $container->shouldReceive('get')->with(PoolFactory::class)->andReturn($factory = new PoolFactory($container));
        $container->shouldReceive('get')->with(DB::class)->andReturn(new DB($factory, 'default'));
        $container->shouldReceive('make')->with(DB::class, Mockery::any())->andReturnUsing(function ($_, $params) use ($factory) {
            return new DB($factory, $params['poolName']);
        });
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturn(false);
        return $container;
    }
}
