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

namespace HyperfTest\ConfigZookeeper;

use Hyperf\Config\Config;
use Hyperf\ConfigZookeeper\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    public function testPull()
    {
        $container = Mockery::mock(ContainerInterface::class);
        // @TODO Add a test env.
        $configInstance = new Config([
            'zookeeper' => [
                'server' => 'localhost:2181',
                'path' => '/conf',
            ],
        ]);
        $configInstance->set('zookeeper.test-key', 'pre-value');
        $container->shouldReceive('get')->with(ClientFactory::class)->andReturn(new ClientFactory($container));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($configInstance);
        ApplicationContext::setContainer($container);
        $client = new Client($container);
        $fetchConfig = $client->pull();
        $this->assertSame('after-value', $fetchConfig['zookeeper.test-key']);
    }
}
