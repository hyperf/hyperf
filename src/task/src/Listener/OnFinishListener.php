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
use Hyperf\Framework\Event\OnFinish;
use Hyperf\Task\ChannelFactory;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
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
        if ($event instanceof OnFinish) {
            $channel = $this->container->get(ChannelFactory::class)->get($event->taskId);

            $channel->push($event->data);
        }
    }
}
