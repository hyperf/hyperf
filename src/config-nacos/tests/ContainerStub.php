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
namespace HyperfTest\ConfigNacos;

use Hyperf\ConfigNacos\Client;
use Hyperf\ConfigNacos\Constants;
use Hyperf\ConfigNacos\NacosClient;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\IPReaderInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Mockery;
use Psr\Container\ContainerInterface;

class ContainerStub
{
    public static function getContainer($handler = null)
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(Application::class)->andReturnUsing(function () use ($handler) {
            return new Application(new Config([
                'guzzle_config' => [
                    'handler' => $handler ?? new HandlerMockery(),
                    'headers' => [
                        'charset' => 'UTF-8',
                    ],
                ],
            ]));
        });

        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new \Hyperf\Config\Config([
            'server' => [
                'servers' => [
                    ['port' => 9501],
                    ['port' => 9502],
                ],
            ],
            'config_center' => [
                'drivers' => [
                    'nacos' => [
                        'merge_mode' => Constants::CONFIG_MERGE_OVERWRITE,
                        'reload_interval' => 1,
                        'default_key' => 'nacos_default_config',
                        'listener_config' => [
                            'nacos_config' => [
                                'tenant' => 'tenant',
                                'data_id' => 'json',
                                'group' => 'DEFAULT_GROUP',
                                'type' => 'json',
                            ],
                            'nacos_config.data' => [
                                'data_id' => 'text',
                                'group' => 'DEFAULT_GROUP',
                            ],
                            [
                                'data_id' => 'json2',
                                'group' => 'DEFAULT_GROUP',
                                'type' => 'json',
                            ],
                        ],
                    ],
                ],
            ],
            'nacos' => [
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'service' => [
                    'enable' => true,
                    'service_name' => 'hyperf',
                    'group_name' => 'api',
                    'namespace_id' => 'namespace_id',
                    'protect_threshold' => 0.5,
                    'metadata' => null,
                    'selector' => null,
                    'instance' => [
                        'ip' => IPReaderInterface::class,
                        'cluster' => null,
                        'weight' => null,
                        'metadata' => null,
                        'ephemeral' => null,
                    ],
                ],
            ],
        ]));

        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturnUsing(function () {
            $logger = Mockery::mock(StdoutLoggerInterface::class);
            $logger->shouldReceive('warning')->andReturnFalse();
            $logger->shouldReceive('info')->andReturnFalse();
            $logger->shouldReceive('critical')->andReturnUsing(function ($message) {
                var_dump($message);
            });
            $logger->shouldReceive('error')->andReturnUsing(function ($message) {
                var_dump($message);
            });
            return $logger;
        });

        $container->shouldReceive('get')->with(NacosClient::class)->andReturnUsing(function () use ($container) {
            return $container->get(Application::class);
        });

        $container->shouldReceive('get')->with(Client::class)->andReturnUsing(function () use ($container) {
            return new Client($container);
        });

        return $container;
    }
}
