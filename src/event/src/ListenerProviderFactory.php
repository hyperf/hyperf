<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Event;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\MessageListener;
use Hyperf\Event\Annotation\TaskListener;
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
        $collector = AnnotationCollector::getContainer();
        foreach ($collector as $className => $values) {
            if (! isset($values['_c'][TaskListener::class]) && ! isset($values['_c'][MessageListener::class])) {
                continue;
            }
            $priority = $values['priority'] ?? 1;
            $this->register($provider, $container, $className, (int)$priority);
        }
    }

    private function register(ListenerProvider $provider, ContainerInterface $container, string $listener, int $priority = 1): void
    {
        $instance = $container->get($listener);
        if (method_exists($instance, 'process')) {
            foreach ($instance->listen() as $event) {
                $provider->on($event, [$instance, 'process'], $priority);
            }
        }
        if (method_exists($instance, 'notify')) {
            foreach ($instance->listen() as $event) {
                $provider->on($event, [$instance, 'notify']);
            }
        }
    }
}
