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
namespace Hyperf\HttpServer\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Server\Event;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class EnableRequestLifecycleListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class);

        foreach ($config->get('server.servers', []) as $serverConfig) {
            if (! ($serverConfig['options']['enable_request_lifecycle'] ?? false)) {
                continue;
            }

            [$serverClass] = $serverConfig['callbacks'][Event::ON_REQUEST] ?? [null];

            if (! $this->container->has($serverClass)) {
                continue;
            }

            $server = $this->container->get($serverClass);

            if (! method_exists($server, 'setEventDispatcher')) {
                continue;
            }

            if (! $this->container->has(EventDispatcherInterface::class)) {
                continue;
            }

            $server->setEventDispatcher($this->container->get(EventDispatcherInterface::class));
        }
    }
}
