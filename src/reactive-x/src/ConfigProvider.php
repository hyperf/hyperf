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

namespace Hyperf\ReactiveX;

use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\Contract\MessageBusInterface;
use Hyperf\ReactiveX\Listener\BootApplicationListener;
use Rx\Scheduler\EventLoopScheduler;
use Rx\SchedulerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                BroadcasterInterface::class => AllProcessesBroadcaster::class,
                MessageBusInterface::class => MessageBusFactory::class,
                SchedulerInterface::class => EventLoopScheduler::class,
            ],
            'listeners' => [
                BootApplicationListener::class,
            ],
        ];
    }
}
