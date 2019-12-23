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

namespace HyperfTest\JsonRpc;

use Hyperf\Config\Config;
use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\Pool\PoolFactory;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonRpcPoolTransporterTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testJsonRpcPoolTransporterConfig()
    {
        $factory = Mockery::mock(PoolFactory::class);
        $config = new Config([
            'json_rpc' => [
                'transporter' => [
                    'tcp' => [
                        'pool' => [
                            'min_connections' => 16,
                        ],
                    ],
                ],
            ],
        ]);
        $transporter = new JsonRpcPoolTransporter($factory, $config, ['pool' => ['min_connections' => 10]]);

        $this->assertSame(10, $transporter->getConfig()['pool']['min_connections']);
        $this->assertSame(32, $transporter->getConfig()['pool']['max_connections']);
    }

    public function testJsonRpcPoolTransporterConfigInterface()
    {
        $factory = Mockery::mock(PoolFactory::class);
        $config = new Config([
            'json_rpc' => [
                'transporter' => [
                    'tcp' => [
                        'options' => $data = [
                            'open_length_check' => true,
                            'package_length_type' => 'N',
                            'package_length_offset' => 0,
                            'package_body_offset' => 4,
                            'package_max_length' => 1024 * 1024 * 2,
                        ],
                        'pool' => [
                            'min_connections' => 16,
                        ],
                    ],
                ],
            ],
        ]);

        $transporter = new JsonRpcPoolTransporter($factory, $config);

        $this->assertSame(16, $transporter->getConfig()['pool']['min_connections']);
        $this->assertSame(32, $transporter->getConfig()['pool']['max_connections']);
        $this->assertSame($data, $transporter->getConfig()['options']);
    }
}
