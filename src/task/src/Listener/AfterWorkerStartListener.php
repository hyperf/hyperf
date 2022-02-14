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
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Task\TaskExecutor;
use Psr\Container\ContainerInterface;

class AfterWorkerStartListener implements ListenerInterface
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
            AfterWorkerStart::class,
        ];
    }

    /**
     * @param AfterWorkerStart $event
     */
    public function process(object $event)
    {
        if ($event instanceof AfterWorkerStart) {
            if (! $event->server->taskworker) {
                $this->container->get(TaskExecutor::class)->setIsTaskEnvironment(false);
            }
        }
    }
}
