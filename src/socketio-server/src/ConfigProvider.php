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
namespace Hyperf\SocketIOServer;

use Hyperf\SocketIOServer\Command\RemoveRedisGarbage;
use Hyperf\SocketIOServer\Listener\AddRouteListener;
use Hyperf\SocketIOServer\Listener\ServerIdListener;
use Hyperf\SocketIOServer\Listener\StartSubscriberListener;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\Room\RedisAdapter;
use Hyperf\SocketIOServer\Room\SubscriberFactory;
use Hyperf\SocketIOServer\SidProvider\DistributedSidProvider;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Mix\Redis\Subscribe\Subscriber;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Subscriber::class => SubscriberFactory::class,
                AdapterInterface::class => RedisAdapter::class,
                SidProviderInterface::class => DistributedSidProvider::class,
            ],
            'listeners' => [
                AddRouteListener::class,
                ServerIdListener::class,
                StartSubscriberListener::class,
            ],
            'commands' => [
                RemoveRedisGarbage::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
