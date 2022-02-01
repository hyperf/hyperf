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
namespace Hyperf\SocketIOServer\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use Hyperf\SocketIOServer\Room\EphemeralInterface;
use Hyperf\SocketIOServer\Room\RedisAdapter;
use Psr\Container\ContainerInterface;

class StartSubscriberListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        foreach (SocketIORouter::get('forward') ?? [] as $class) {
            $instance = $this->container->get($class);
            $adapter = $instance->getAdapter();
            if ($adapter instanceof RedisAdapter) {
                $adapter->subscribe();
            }
            if ($adapter instanceof EphemeralInterface) {
                $adapter->cleanUpExpired();
            }
        }
    }
}
