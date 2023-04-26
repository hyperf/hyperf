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
namespace HyperfTest\ConfigEtcd;

use Hyperf\Config\Config;
use Hyperf\ConfigEtcd\KVFactory;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Support\Reflection\ClassInvoker;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class KVFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testMakeKVClientFromETCD()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(HandlerStackFactory::class)->andReturn(Mockery::mock(HandlerStackFactory::class));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'etcd' => [
                'uri' => 'http://127.0.0.1:2379',
                'version' => 'v3beta',
                'options' => [
                    'timeout' => 10,
                ],
            ],
        ]));

        $factory = new KVFactory();
        $client = new ClassInvoker($factory($container));
        $this->assertSame('http://127.0.0.1:2379/v3beta/', $client->baseUri);
    }

    public function testMakeKVClientFromConfigCenter()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(HandlerStackFactory::class)->andReturn(Mockery::mock(HandlerStackFactory::class));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'config_center' => [
                'drivers' => [
                    'etcd' => [
                        'client' => [
                            'uri' => 'http://localhost:2379',
                            'version' => 'v3beta',
                            'options' => [
                                'timeout' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            'etcd' => [
                'uri' => 'http://127.0.0.1:2379',
                'version' => 'v3beta',
                'options' => [
                    'timeout' => 10,
                ],
            ],
        ]));

        $factory = new KVFactory();
        $client = new ClassInvoker($factory($container));
        $this->assertSame('http://localhost:2379/v3beta/', $client->baseUri);
    }
}
