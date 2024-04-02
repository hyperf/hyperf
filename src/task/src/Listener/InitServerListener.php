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

namespace Hyperf\Task\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Task\TaskExecutor;
use Psr\Container\ContainerInterface;

class InitServerListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof BeforeMainServerStart) {
            if (! $this->container->has(TaskExecutor::class)) {
                return;
            }
            $executor = $this->container->get(TaskExecutor::class);
            $executor->setServer($event->server);
        }
    }
}
