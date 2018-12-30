<?php

namespace Hyperf\Event;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\MessageListenerInterface;
use Hyperf\Event\Contract\TaskListenerInterface;
use Psr\Container\ContainerInterface;

class ListenerProviderFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $listenerProvider = new ListenerProvider();

        // Register config listeners.
        $this->registerConfig($listenerProvider, $container);

        // Register annotation listeners.
        $this->registerAnnotations($listenerProvider);

        return $listenerProvider;
    }

    private function registerConfig(ListenerProvider $provider,  ContainerInterface $container): void
    {
        $config = $container->get(ConfigInterface::class);
        foreach ($config->get('listeners', []) as $listener) {
            if (is_string($listener)) {
                $instance = $container->get($listener);
                if ($instance instanceof TaskListenerInterface) {
                    foreach ($instance->listen() as $event) {
                        $provider->on($event, [$instance, 'process']);
                    }
                }
                if ($instance instanceof MessageListenerInterface) {
                    foreach ($instance->listen() as $event) {
                        $provider->on($event, [$instance, 'process']);
                    }
                }
            }
        }

    }

    private function registerAnnotations(ListenerProvider $listenerProvider): void
    {
    }

}