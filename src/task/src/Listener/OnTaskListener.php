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
use Hyperf\Framework\Event\OnTask;
use Hyperf\Task\Exception;
use Hyperf\Task\Finish;
use Hyperf\Task\Task;
use Psr\Container\ContainerInterface;

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

            try {
                $result = $this->call($data);
                $this->setResult($event, $result);
            } catch (\Throwable $throwable) {
                $this->setResult($event, new Exception($this->container, $throwable));
            }
        }
    }

    protected function call(Task $data)
    {
        if (is_array($data->callback)) {
            [$class, $method] = $data->callback;
            if ($this->container->has($class)) {
                $obj = $this->container->get($class);
                return $obj->{$method}(...$data->arguments);
            }
        }

        return call($data->callback, $data->arguments);
    }

    protected function setResult(OnTask $event, $result)
    {
        $event->setResult(new Finish($result));
    }
}
