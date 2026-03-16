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

namespace Hyperf\Command\Listener;

use Hyperf\Command\Event\FailToHandle;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

class FailToHandleListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            FailToHandle::class,
        ];
    }

    /**
     * @param FailToHandle $event
     */
    public function process(object $event): void
    {
        if ($this->container->has(StdoutLoggerInterface::class) && $logger = $this->container->get(StdoutLoggerInterface::class)) {
            $logger->error((string) $event->getThrowable());
        }
    }
}
