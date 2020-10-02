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
use Hyperf\Framework\Event\OnFinish;
use Hyperf\Task\ChannelFactory;
use Hyperf\Task\Finish;
use Psr\Container\ContainerInterface;

class OnFinishListener implements ListenerInterface
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
            OnFinish::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof OnFinish && $event->data instanceof Finish) {
            $factory = $this->container->get(ChannelFactory::class);
            $factory->push($event->taskId, $event->data->data);
        }
    }
}
