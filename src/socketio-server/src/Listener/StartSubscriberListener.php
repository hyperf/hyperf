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
use Hyperf\Utils\ApplicationContext;

class StartSubscriberListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(object $event)
    {
        foreach (IORouter::get('forward') as $class) {
            $instance = ApplicationContext::getContainer()->get($class);
            if ($instance->getAdapter() instanceof RedisAdapter) {
                $instance->getAdapter()->subscribe();
            }
        }
    }
}
