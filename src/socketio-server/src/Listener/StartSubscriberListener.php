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
namespace Hyperf\SocketIOServer\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\SocketIOServer\Collector\IORouter;
use Hyperf\SocketIOServer\Room\RedisAdapter;
use Psr\Container\ContainerInterface;

class StartSubscriberListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        foreach (IORouter::get('forward') as $class) {
            $instance = $this->container->get($class);
            if ($instance->getAdapter() instanceof RedisAdapter) {
                $instance->getAdapter()->subscribe();
            }
        }
    }
}
