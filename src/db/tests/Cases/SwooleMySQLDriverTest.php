<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\DB\Cases;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DB\DB;
use Hyperf\DB\Pool\PoolFactory;
use Hyperf\DB\Pool\SwooleMySqlPool;
use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use Mockery;

/**
 * @internal
 * @coversNothing
 */
class SwooleMySQLDriverTest extends AbstractTestCase
{
    public function testSwooleMySQL()
    {
        $connect = $this->getSwooleMySqlDB();
        $stmt = $connect->prepare('INSERT INTO `test`(`a`,`b`,`c`) VALUES (?,?,?)', [1, 2, 3]);
        $this->assertSame(true, $stmt);

        $testList = $connect->query('SELECT * FROM `test`');
        $this->assertNotNull($testList);

        // rollback test
        $connect->beginTransaction();

        $connect->prepare('INSERT INTO `test`(`a`,`b`,`c`) VALUES (?,?,?)', [9, 9, 9]);

        $connect->rollback();

        // commit test
        $connect->beginTransaction();

        $connect->prepare('INSERT INTO `test`(`a`,`b`,`c`) VALUES (?,?,?)', [8, 8, 8]);

        $connect->commit();

        // transaction Nesting test
        $connect->beginTransaction();

        $connect->prepare('INSERT INTO `test`(`a`,`b`,`c`) VALUES (?,?,?)', [6, 6, 6]);

        $connect->beginTransaction();
        $connect->prepare('INSERT INTO `test`(`a`,`b`,`c`) VALUES (?,?,?)', [7, 7, 7]);
        $connect->rollback();
        $connect->prepare('INSERT INTO `test`(`a`,`b`,`c`) VALUES (?,?,?)', [5, 5, 5]);

        $connect->commit();

        var_dump($connect->getLastInsertId());
        var_dump($connect->getErrorCode());
        var_dump($connect->getErrorInfo());
    }

    /**
     * @return DB
     */
    public function getSwooleMySQLDB()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'database' => [
                'default' => [
                    'driver' => env('DB_DRIVER', 'swoole_mysql'),
                    'host' => env('DB_HOST', 'localhost'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => env('DB_DATABASE', 'test'),
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', 'root'),
                    'charset' => env('DB_CHARSET', 'utf8'),
                    'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
                    'prefix' => env('DB_PREFIX', ''),
                    'pool' => [
                        'min_connections' => 1,
                        'max_connections' => 10,
                        'connect_timeout' => 10.0,
                        'wait_timeout' => 3.0,
                        'heartbeat' => -1,
                        'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
                    ],
                ],
            ],
        ]));
        $pool = new SwooleMySQLPool($container, 'default');
        $container->shouldReceive('make')->once()->with(SwooleMySQLPool::class, ['name' => 'default'])->andReturn($pool);

        ApplicationContext::setContainer($container);
        $factory = new PoolFactory($container);
        return new DB($factory);
    }
}
