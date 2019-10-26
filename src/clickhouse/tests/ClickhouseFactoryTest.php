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

namespace HyperfTest\Clickhouse;

use ClickHouseDB\Client;
use Hyperf\Clickhouse\ClickhouseFactory;
use Hyperf\Clickhouse\Exception\NotHookException;
use Hyperf\Config\Config;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClickhouseFactoryTest extends TestCase
{
    public function testNotHookCurl()
    {
        $config = new Config([
            'clickhouse' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => '8123',
                    'username' => 'default',
                    'password' => '',
                    'settings' => [
                        'database' => 'default',
                    ],
                ],
            ],
        ]);

        $factory = new ClickhouseFactory($config);

        // $this->expectException(NotHookException::class);

        $db = $factory->create();

        $this->assertInstanceOf(Client::class, $db);
    }

    public function testEnable()
    {
        $flag = SWOOLE_HOOK_ALL;
        $this->assertTrue(($flag & SWOOLE_HOOK_CURL) === 0);

        $flag = SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL;
        $this->assertTrue(($flag & SWOOLE_HOOK_CURL) !== 0);

        $flag = SWOOLE_HOOK_ALL | SWOOLE_HOOK_BLOCKING_FUNCTION;
        $this->assertTrue(($flag & SWOOLE_HOOK_CURL) === 0);
    }
}
