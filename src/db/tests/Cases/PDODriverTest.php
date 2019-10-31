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

namespace HyperfTest\Cases;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DB\DB;
use Hyperf\DB\Pool\PDOPool;
use Hyperf\DB\Pool\PoolFactory;
use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Cases\Stub\PDOPoolStud;
use Mockery;

/**
 * @internal
 * @coversNothing
 */
class PDODriverTest extends AbstractTestCase
{
    public function testPDO()
    {
        $connect = $this->getPDODB();
        $result = $connect->prepare("INSERT INTO `test`(`a`,`b`,`c`) VALUES (?,?,?)")->execute([1, 2, 3]);

        $this->assertSame(true, $result);

        $testList = $connect->query("SELECT * FROM `test`");
        $this->assertNotNull($testList);
    }

    public function getPDODB()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'database' => [
                'default' => [
                    'driver' => env('DB_DRIVER', 'mysql'),
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
                        'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
                    ],
                ],
            ],
        ]));
        $pool = new PDOPool($container, 'default');
        $container->shouldReceive('make')->once()->with(PDOPool::class, ['name' => 'default'])->andReturn($pool);

        ApplicationContext::setContainer($container);
        $factory = new PoolFactory($container);
        return new DB($factory);
    }
}
