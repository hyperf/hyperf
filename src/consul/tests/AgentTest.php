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

namespace HyperfTest\Consul;

use GuzzleHttp\Client;
use Hyperf\Consul\Agent;
use Hyperf\Consul\AgentInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Guzzle\ClientFactory;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(Agent::class)]
class AgentTest extends TestCase
{
    /**
     * @var AgentInterface
     */
    private $agent;

    protected function setUp(): void
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
                return new Client($options['config']);
            }
        });
        ApplicationContext::setContainer($container);
        return new Agent(function () use ($container) {
            return $container->get(ClientFactory::class)->create([
                'base_uri' => Agent::DEFAULT_URI,
            ]);
        }, $container->get(StdoutLoggerInterface::class));
    }
}
