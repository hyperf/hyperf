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

namespace Hyperf\Process\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\AfterCoroutineHandle;
use Hyperf\Process\Event\AfterProcessHandle;
use Psr\Container\ContainerInterface;

class LogAfterProcessStoppedListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            AfterProcessHandle::class,
            AfterCoroutineHandle::class,
        ];
    }

    /**
     * @param AfterProcessHandle $event
     */
    public function process(object $event): void
    {
        $message = sprintf('Process[%s.%d] stopped.', $event->process->name, $event->index);
        if ($this->container->has(StdoutLoggerInterface::class)) {
            $logger = $this->container->get(StdoutLoggerInterface::class);
            $logger->info($message);
        } else {
            echo $message . PHP_EOL;
        }
    }
}
