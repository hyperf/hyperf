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
namespace Hyperf\ModelListener\Listener;

use Hyperf\Database\Model\Events\Event;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ModelListener\Collector\ListenerCollector;
use Psr\Container\ContainerInterface;

class ModelEventListener implements ListenerInterface
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
            Event::class,
        ];
    }

    /**
     * @param Event $event
     */
    public function process(object $event)
    {
        $model = $event->getModel();
        $modelName = get_class($model);

        $listeners = ListenerCollector::getListenersForModel($modelName);
        foreach ($listeners as $name) {
            if (! $this->container->has($name)) {
                continue;
            }

            $listener = $this->container->get($name);
            if (method_exists($listener, $event->getMethod())) {
                $listener->{$event->getMethod()}($event);
            }
        }
    }
}
