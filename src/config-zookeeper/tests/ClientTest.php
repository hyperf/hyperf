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

namespace HyperfTest\ConfigZookeeper;

use Hyperf\Config\Config;
use Hyperf\ConfigCenter\PipeMessage;
use Hyperf\ConfigZookeeper\ClientInterface;
use Hyperf\ConfigZookeeper\ZookeeperDriver;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Guzzle\ClientFactory;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\value;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ClientTest extends TestCase
{
    public function testPull()
    {
        $container = $this->getContainer();
        $client = $container->get(ClientInterface::class);
        $fetchConfig = $client->pull();
        $this->assertSame('after-value', $fetchConfig['zookeeper.test-key']);
    }

    public function testOnPipeMessageListener()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(value(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->with(Mockery::any())->andReturnUsing(function ($args) {
                $this->assertSame('Config [zookeeper.test-key] is updated', $args);
            });

            return $logger;
        }));
        $driver = new ZookeeperDriver($container);
        $client = $container->get(ClientInterface::class);
        $config = $client->pull();
        $event = Mockery::mock(OnPipeMessage::class);
        $event->data = new PipeMessage($config);
        $config = $container->get(ConfigInterface::class);
        $this->assertSame('pre-value', $config->get('zookeeper.test-key'));
        $driver->onPipeMessage($event->data);
        $this->assertSame('after-value', $config->get('zookeeper.test-key'));
    }

    public function getContainer()
    {
        $container = Mockery::mock(Container::class);
        // @TODO Add a test env.
        $configInstance = new Config([
            'zookeeper' => [
                'server' => 'localhost:2181',
                'path' => '/conf',
            ],
        ]);
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('pull')->andReturn([
            'zookeeper.test-key' => 'after-value',
        ]);
        $configInstance->set('zookeeper.test-key', 'pre-value');
        $container->shouldReceive('get')->with(ClientFactory::class)->andReturn(new ClientFactory($container));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($configInstance);
        $container->shouldReceive('get')->with(ClientInterface::class)->andReturn($client);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
