<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Task\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnTask;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
class OnTaskListener implements ListenerInterface
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
            OnTask::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof OnTask && $data = $event->task->data) {
            if (! $data instanceof Task) {
                return;
            }

            $executor = $this->container->get(TaskExecutor::class);
            $executor->setIsTaskEnvironment(true);

            if (is_array($data->callback)) {
                [$class, $method] = $data->callback;
                if ($this->container->has($class)) {
                    $obj = $this->container->get($class);
                    $result = $obj->{$method}(...$data->arguments);
                    $event->setResult($result);
                    return;
                }
            }

            $result = call($data->callback, $data->arguments);
            $event->setResult($result);
        }
    }
}
