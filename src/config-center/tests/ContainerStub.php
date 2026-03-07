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

namespace HyperfTest\ConfigCenter;

use Hyperf\Codec\Json;
use Hyperf\Codec\Packer\JsonPacker;
use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\ConfigEtcd;
use Hyperf\ConfigNacos;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Etcd\KVInterface;
use Mockery;
use Psr\EventDispatcher\EventDispatcherInterface;

class ContainerStub
{
    public static function mockContainer(ConfigInterface $config)
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(new class {
            public function dispatch()
            {
                return true;
            }
        });
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('debug')->andReturnFalse();
            return $logger;
        });
        $container->shouldReceive('get')->with(DriverFactory::class)->andReturn(new DriverFactory($config));
        $container->shouldReceive('make')->with(ConfigEtcd\EtcdDriver::class, Mockery::any())->andReturnUsing(function () use ($container) {
            return new ConfigEtcd\EtcdDriver($container);
        });
        $container->shouldReceive('get')->with(ConfigEtcd\ClientInterface::class)->andReturnUsing(function () use ($container) {
            return new ConfigEtcd\Client(
                $container->get(KVInterface::class),
                $container->get(ConfigInterface::class)
            );
        });
        $container->shouldReceive('get')->with(KVInterface::class)->andReturnUsing(function () {
            $kv = Mockery::mock(KVInterface::class);
            $kv->shouldReceive('fetchByPrefix')->withAnyArgs()->andReturn(
                Json::decode(file_get_contents(__DIR__ . '/json/etcd.kv.json'))
            );
            return $kv;
        });
        $container->shouldReceive('make')->with(ConfigNacos\NacosDriver::class)->withAnyArgs()->andReturnUsing(function () use ($container) {
            return new ConfigNacos\NacosDriver($container);
        });
        $container->shouldReceive('get')->with(ConfigNacos\ClientInterface::class)->andReturnUsing(function () {
            $client = Mockery::mock(ConfigNacos\ClientInterface::class);
            $client->shouldReceive('pull')->andReturn([
                'test' => [
                    'message' => 'Hello Hyperf',
                    'id' => 1,
                ],
            ]);
            return $client;
        });
        $container->shouldReceive('get')->with(JsonPacker::class)->andReturn(new JsonPacker());
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        return $container;
    }
}
