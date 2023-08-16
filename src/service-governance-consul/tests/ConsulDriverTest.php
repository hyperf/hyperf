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
namespace HyperfTest\ServiceGovernanceConsul;

use Hyperf\Config\Config;
use Hyperf\Consul\ConsulResponse;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ServiceGovernanceConsul\ConsulAgent;
use Hyperf\ServiceGovernanceConsul\ConsulDriver;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ConsulDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCheckConfig()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'services' => [
                'enable' => [
                    'discovery' => true,
                    'register' => true,
                ],
                'consumers' => [],
                'providers' => [],
                'drivers' => [
                    'consul' => [
                        'uri' => 'http://127.0.0.1:8500',
                        'token' => '',
                        'check' => [
                            'deregister_critical_service_after' => '88m',
                            'interval' => '2s',
                        ],
                    ],
                ],
            ],
        ]));
        $agent = Mockery::mock(ConsulAgent::class);
        $agent->shouldReceive('registerService')->once()->withAnyArgs()->andReturnUsing(function ($args) {
            $this->assertSame('88m', $args['Check']['DeregisterCriticalServiceAfter']);
            $this->assertSame('2s', $args['Check']['Interval']);
            $response = Mockery::mock(ConsulResponse::class);
            $response->shouldReceive('getStatusCode')->andReturn(200);
            return $response;
        });
        $agent->shouldReceive('services')->andReturn($response = Mockery::mock(ConsulResponse::class));
        $response->shouldReceive('json')->andReturn([]);
        $container->shouldReceive('get')->with(ConsulAgent::class)->andReturn($agent);
        $driver = new ConsulDriver($container);
        $driver->register('FooService', '127.0.0.1', 9501, ['protocol' => 'jsonrpc']);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('info')->andReturnNull();
            return $logger;
        });
        ApplicationContext::setContainer($container);

        return $container;
    }
}
