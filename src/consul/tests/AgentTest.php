<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Consul;

use Mockery;
use GuzzleHttp\Client;
use Psr\Log\NullLogger;
use Hyperf\Consul\Agent;
use Hyperf\Di\Container;
use PHPUnit\Framework\TestCase;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Consul\AgentInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * @internal
 * @covers \Hyperf\Consul\Agent
 */
class AgentTest extends TestCase
{
    /**
     * @var AgentInterface
     */
    private $agent;

    protected function setUp()
    {
        $this->agent = $this->createAgent();
    }

    public function testChecks()
    {
        $response = $this->agent->checks();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($response->json());
    }

    public function testMembers()
    {
        $response = $this->agent->members();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($response->json());
    }

    public function testServices()
    {
        $response = $this->agent->services();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($response->json());
    }

    public function testSelf()
    {
        $response = $this->agent->self();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertIsArray($response->json());
    }

    private function createAgent(): AgentInterface
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(new NullLogger());
        $container->shouldReceive('get')->with(ClientFactory::class)->andReturn(new ClientFactory($container));
        $container->shouldReceive('make')->andReturnUsing(function ($name, $options) {
            if ($name === Client::class) {
                return new Client($options);
            }
        });
        ApplicationContext::setContainer($container);
        return new Agent(function () use ($container) {
            return $container->get(ClientFactory::class)->create();
        }, $container->get(StdoutLoggerInterface::class));
    }
}
