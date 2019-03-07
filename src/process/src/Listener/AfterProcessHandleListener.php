<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Process\Listener;

use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\AfterProcessHandle;

/**
 * @Listener
 */
class AfterProcessHandleListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            AfterProcessHandle::class,
        ];
    }

    /**
     * @param AfterProcessHandle $event
     */
    public function process(object $event)
    {
        $message = sprintf('Process[%s.%d] stoped.', $event->process->name, $event->index);
        if ($this->container->has(StdoutLoggerInterface::class)) {
            $logger = $this->container->get(StdoutLoggerInterface::class);
            $logger->info($message);
        } else {
            echo $message . PHP_EOL;
        }
    }
}
