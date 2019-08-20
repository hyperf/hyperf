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

namespace Hyperf\ModelObserver\Listener;

use Hyperf\Database\Model\Events\Event;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ModelObserver\Collector\ObserverCollector;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
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
     * @return Event
     */
    public function process(object $event)
    {
        $model = $event->getModel();
        $modelName = get_class($model);

        $observers = ObserverCollector::getObservables($modelName);
        foreach ($observers as $name) {
            if (! $this->container->has($name)) {
                continue;
            }

            $observer = $this->container->get($name);
            if (method_exists($observer, $event->getMethod())) {
                $observer->{$event->getMethod()}($event);
            }
        }

        return $event;
    }
}
