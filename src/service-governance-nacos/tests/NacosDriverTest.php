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

namespace HyperfTest\ServiceGovernanceNacos;

use ErrorException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Config as NacosConfig;
use Hyperf\ServiceGovernanceNacos\Client;
use Hyperf\ServiceGovernanceNacos\NacosDriver;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class NacosDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetsNodesFromNacosV3InstanceListPayload(): void
    {
        $driver = $this->createDriver('3.0', [
            'code' => 0,
            'message' => 'success',
            'data' => [
                [
                    'ip' => '127.0.0.1',
                    'port' => 9501,
                    'weight' => 0.5,
                    'healthy' => true,
                    'enabled' => true,
                ],
                [
                    'ip' => '127.0.0.2',
                    'port' => 9502,
                    'weight' => 1,
                    'healthy' => false,
                    'enabled' => true,
                ],
            ],
        ]);

        $this->assertSame([
            [
                'host' => '127.0.0.1',
                'port' => 9501,
                'weight' => 50,
            ],
        ], $driver->getNodes('', 'demo-service', []));
    }

    public function testGetsNodesFromNacosV2InstanceListPayload(): void
    {
        $driver = $this->createDriver('2.0', [
            'code' => 0,
            'message' => 'success',
            'data' => [
                'name' => 'DEFAULT_GROUP@@demo-service',
                'groupName' => 'DEFAULT_GROUP',
                'hosts' => [
                    [
                        'ip' => '127.0.0.1',
                        'port' => 9501,
                        'weight' => 0.5,
                        'healthy' => true,
                        'enabled' => true,
                    ],
                    [
                        'ip' => '127.0.0.2',
                        'port' => 9502,
                        'weight' => 1,
                        'healthy' => false,
                        'enabled' => true,
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            [
                'host' => '127.0.0.1',
                'port' => 9501,
                'weight' => 50,
            ],
        ], $driver->getNodes('', 'demo-service', []));
    }

    public function testGetsNodesFromNacosV1InstanceListPayload(): void
    {
        $driver = $this->createDriver('1.0', [
            'name' => 'DEFAULT_GROUP@@demo-service',
            'groupName' => 'DEFAULT_GROUP',
            'hosts' => [
                [
                    'ip' => '127.0.0.1',
                    'port' => 9501,
                    'weight' => 0.5,
                    'healthy' => true,
                    'enabled' => true,
                ],
                [
                    'ip' => '127.0.0.2',
                    'port' => 9502,
                    'weight' => 1,
                    'healthy' => false,
                    'enabled' => true,
                ],
            ],
        ]);

        $this->assertSame([
            [
                'host' => '127.0.0.1',
                'port' => 9501,
                'weight' => 50,
            ],
        ], $driver->getNodes('', 'demo-service', []));
    }

    public function testIgnoresNonArrayInstanceListData(): void
    {
        $driver = $this->createDriver('3.0', [
            'code' => 21000,
            'message' => 'service name error',
            'data' => 'invalid instance list',
        ]);

        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, 0, $severity);
        });

        try {
            $this->assertSame([], $driver->getNodes('', 'demo-service', []));
        } finally {
            restore_error_handler();
        }
    }

    private function createDriver(string $version, array $payload): NacosDriver
    {
        $client = new Client(new NacosConfig([
            'base_uri' => 'http://127.0.0.1:8848',
            'version' => $version,
            'guzzle_config' => [
                'handler' => new MockHandler([
                    new Response(200, [], json_encode($payload, JSON_THROW_ON_ERROR)),
                ]),
            ],
        ]));
        $config = new Config([
            'services' => [
                'drivers' => [
                    'nacos' => [
                        'group_name' => 'DEFAULT_GROUP',
                        'namespace_id' => 'public',
                    ],
                ],
            ],
        ]);
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(Client::class)->andReturn($client);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(
            Mockery::mock(StdoutLoggerInterface::class)
        );
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        return new NacosDriver($container);
    }
}
