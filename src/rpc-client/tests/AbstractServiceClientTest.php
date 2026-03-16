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

namespace HyperfTest\RpcClient;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\ServiceGovernance\DriverInterface;
use Hyperf\ServiceGovernance\DriverManager;
use HyperfTest\RpcClient\Stub\FooServiceClient;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AbstractServiceClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateNodesWithRegistry()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->withAnyArgs()->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'services' => [
                'consumers' => [
                    [
                        'name' => 'FooService',
                        'registry' => [
                            'protocol' => 'test',
                            'address' => 'http://127.0.0.1:8848',
                        ],
                    ],
                ],
            ],
        ]));
        $container->shouldReceive('get')->with(DriverManager::class)->andReturn($manager = Mockery::mock(DriverManager::class));
        $manager->shouldReceive('get')->with('test')->andReturnUsing(function () {
            $driver = Mockery::mock(DriverInterface::class);
            $driver->shouldReceive('isLongPolling')->andReturnFalse();
            $driver->shouldReceive('getNodes')->andReturn([
                ['host' => '192.168.1.1', 'port' => 9501],
                ['host' => '192.168.1.2', 'port' => 9501],
            ]);
            return $driver;
        });

        $client = new FooServiceClient($container);
        [$nodes] = $client->createNodes();
        $this->assertSame(2, count($nodes));
        $this->assertSame('192.168.1.1', $nodes[0]->host);
    }

    public function testCreateNodesWithoutRegistry()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->withAnyArgs()->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'services' => [
                'consumers' => [
                    [
                        'name' => 'FooService',
                        'nodes' => [
                            ['host' => '192.168.1.2', 'port' => 9501],
                        ],
                    ],
                ],
            ],
        ]));

        $client = new FooServiceClient($container);
        [$nodes] = $client->createNodes();
        $this->assertSame(1, count($nodes));
        $this->assertSame('192.168.1.2', $nodes[0]->host);
        $this->assertSame('FooService', $client->getServiceName());
    }
}
