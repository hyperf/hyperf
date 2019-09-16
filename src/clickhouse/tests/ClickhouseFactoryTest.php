<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Clickhouse;

use ClickHouseDB\Client;
use Hyperf\Clickhouse\ClickhouseFactory;
use Hyperf\Config\Config;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClickhouseFactoryTest extends TestCase
{
    public function testCreate()
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

        $db = $factory->create();

        $this->assertInstanceOf(Client::class, $db);
    }
}
