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
namespace HyperfTest\ServiceGovernance\Listener;

use GuzzleHttp\Psr7\Response;
use Hyperf\Config\Config;
use Hyperf\Consul\ConsulResponse;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Logger\Logger;
use Hyperf\ServiceGovernance\Listener\RegisterServiceListener;
use Hyperf\ServiceGovernance\Register\ConsulAgent;
use Hyperf\ServiceGovernance\ServiceManager;
use Mockery;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class RegisterServiceListenerTest extends TestCase
{
    public function testRegisterOnceForTheSameService()
    {
        $container = $this->createContainer();
        $serviceDefinition = null;
        $listener = new RegisterServiceListener($container);
        $mockAgent = $container->get(ConsulAgent::class);
        $mockAgent->shouldReceive('registerService')
            ->once()
            ->with(Mockery::on(function ($args) use (&$serviceDefinition) {
                $serviceDefinition = $args;
                return true;
            }))
            ->andReturn(new ConsulResponse(new Response(200, ['content-type' => 'application/json'])));
        $serviceManager = $container->get(ServiceManager::class);
        $serviceManager->register('Foo\\FooService', 'Foo/FooService/foo', [
            'publishTo' => 'consul',
            'server' => 'jsonrpc-http',
            'protocol' => 'jsonrpc-http',
        ]);
        $serviceManager->register('Foo\\FooService', 'Foo/FooService/bar', [
            'publishTo' => 'consul',
            'server' => 'jsonrpc-http',
            'protocol' => 'jsonrpc-http',
        ]);
        $listener->process((object) []);
        $this->assertEquals('Foo\\FooService', $serviceDefinition['Name']);
        $this->assertEquals(['Protocol' => 'jsonrpc-http'], $serviceDefinition['Meta']);
        $this->assertArrayHasKey('Check', $serviceDefinition);
        $this->assertArrayHasKey('HTTP', $serviceDefinition['Check']);
    }

    public function testRegisterForTheSameServiceWithoutTheSameProtocol()
    {
        $container = $this->createContainer();
        $serviceDefinition = [];
        $listener = new RegisterServiceListener($container);
        $mockAgent = $container->get(ConsulAgent::class);
        $mockAgent->shouldReceive('registerService')
            ->times(3)
            ->with(Mockery::on(function ($args) use (&$serviceDefinition) {
                $serviceDefinition[] = $args;
                return true;
            }))
            ->andReturn(new ConsulResponse(new Response(200, ['content-type' => 'application/json'])));
        $serviceManager = $container->get(ServiceManager::class);
        $serviceManager->register('Foo\\FooService', 'Foo/FooService/foo', [
            'publishTo' => 'consul',
            'server' => 'jsonrpc-http',
            'protocol' => 'jsonrpc-http',
        ]);
        $serviceManager->register('Foo\\FooService', 'Foo/FooService/foo', [
            'publishTo' => 'consul',
            'server' => 'jsonrpc',
            'protocol' => 'jsonrpc',
        ]);
        $serviceManager->register('Foo\\FooService', 'Foo/FooService/foo', [
            'publishTo' => 'consul',
            'server' => 'jsonrpc2',
            'protocol' => 'jsonrpc-tcp-length-check',
        ]);
        $listener->process((object) []);

        $this->assertEquals('Foo\\FooService', $serviceDefinition[0]['Name']);
        $this->assertEquals(['Protocol' => 'jsonrpc-http'], $serviceDefinition[0]['Meta']);
        $this->assertArrayHasKey('Check', $serviceDefinition[0]);
        $this->assertArrayHasKey('HTTP', $serviceDefinition[0]['Check']);

        $this->assertEquals('Foo\\FooService', $serviceDefinition[1]['Name']);
        $this->assertEquals(['Protocol' => 'jsonrpc'], $serviceDefinition[1]['Meta']);
        $this->assertArrayHasKey('Check', $serviceDefinition[1]);
        $this->assertArrayHasKey('TCP', $serviceDefinition[1]['Check']);

        $this->assertEquals('Foo\\FooService', $serviceDefinition[2]['Name']);
        $this->assertEquals(['Protocol' => 'jsonrpc-tcp-length-check'], $serviceDefinition[2]['Meta']);
        $this->assertArrayHasKey('Check', $serviceDefinition[2]);
        $this->assertArrayHasKey('TCP', $serviceDefinition[2]['Check']);
    }

    public function testPublishDisable()
    {
        $container = $this->createContainer(false);
        $listener = new class($container) extends RegisterServiceListener {
            protected function getServers(): array
            {
                throw new \RuntimeException('service_governance.publish.enable does not works.');
            }
        };
        $listener->process((object) []);
        $this->assertTrue(true);
    }

    private function createContainer($enable = true)
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConsulAgent::class)
            ->andReturn($mockAgent = Mockery::mock(ConsulAgent::class));
        $mockAgent->shouldReceive('services')
            ->andReturn(new ConsulResponse(new Response(200, ['content-type' => 'application/json'], '{}')));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)
            ->andReturn(new Logger('App', [
                new StreamHandler('/dev/null'),
            ]));
        $container->shouldReceive('get')->with(ServiceManager::class)
            ->andReturn(new ServiceManager());
        $container->shouldReceive('get')->with(ConfigInterface::class)
            ->andReturn(new Config([
                'server' => [
                    'servers' => [
                        [
                            'name' => 'jsonrpc-http',
                            'host' => '0.0.0.0',
                            'port' => 9501,
                        ],
                        [
                            'name' => 'jsonrpc',
                            'host' => '0.0.0.0',
                            'port' => 9502,
                        ],
                        [
                            'name' => 'jsonrpc2',
                            'host' => '0.0.0.0',
                            'port' => 9503,
                        ],
                    ],
                ],
                'service_governance' => [
                    'publish' => [
                        'enable' => $enable,
                    ],
                ],
            ]));
        return $container;
    }
}
