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

namespace Hyperf\Cache\Listener;

use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
class DeleteListener implements ListenerInterface
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
            DeleteEvent::class,
        ];
    }

    /**
     * @param DeleteEvent $event
     */
    public function process(object $event)
    {
        $className = $event->getClassName();
        $method = $event->getMethod();
        $arguments = $event->getArguments();

        $manager = $this->container->get(CacheManager::class);

        [$key, , $group] = $manager->getAnnotationValue($className, $method, $arguments);

        /** @var DriverInterface $driver */
        $driver = $manager->getDriver($group);
        $driver->delete($key);
    }
}
