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
use Hyperf\Process\Event\BeforeCoroutineHandle;
use Hyperf\Process\Event\BeforeProcessHandle;
use Psr\Container\ContainerInterface;

class LogBeforeProcessStartListener implements ListenerInterface
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
            BeforeProcessHandle::class,
            BeforeCoroutineHandle::class,
        ];
    }

    /**
     * @param BeforeProcessHandle $event
     */
    public function process(object $event)
    {
        $message = sprintf('Process[%s.%d] start.', $event->process->name, $event->index);
        if ($this->container->has(StdoutLoggerInterface::class)) {
            $logger = $this->container->get(StdoutLoggerInterface::class);
            $logger->info($message);
        } else {
            echo $message . PHP_EOL;
        }
    }
}
