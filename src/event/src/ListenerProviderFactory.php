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
namespace Hyperf\Event;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

class ListenerProviderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $listenerProvider = new ListenerProvider();

        // Register config listeners.
        $this->registerConfig($listenerProvider, $container);

        // Register annotation listeners.
        $this->registerAnnotations($listenerProvider, $container);

        return $listenerProvider;
    }

    private function registerConfig(ListenerProvider $provider, ContainerInterface $container): void
    {
        $config = $container->get(ConfigInterface::class);
        foreach ($config->get('listeners', []) as $listener => $priority) {
            if (is_int($listener)) {
                $listener = $priority;
                $priority = 1;
            }
            if (is_string($listener)) {
                $this->register($provider, $container, $listener, $priority);
            }
        }
    }

    private function registerAnnotations(ListenerProvider $provider, ContainerInterface $container): void
    {
        foreach (AnnotationCollector::list() as $className => $values) {
            /** @var Listener $annotation */
            if ($annotation = $values['_c'][Listener::class] ?? null) {
                $this->register($provider, $container, $className, (int) $annotation->priority);
            }
        }
    }

    private function register(ListenerProvider $provider, ContainerInterface $container, string $listener, int $priority = 1): void
    {
        $instance = $container->get($listener);
        if ($instance instanceof ListenerInterface) {
            foreach ($instance->listen() as $event) {
                $provider->on($event, [$instance, 'process'], $priority);
            }
        }
    }
}
