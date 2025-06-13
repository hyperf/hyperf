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

namespace Hyperf\SocketIOServer\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Server\ServerInterface;
use Hyperf\SocketIOServer\SocketIO;
use Psr\Container\ContainerInterface;

class AddRouteListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param BeforeMainServerStart $event
     */
    public function process(object $event): void
    {
        $serverConfig = $this->container->get(ConfigInterface::class)->get('server.servers', []);
        foreach ($serverConfig as $port) {
            if ($port['type'] === ServerInterface::SERVER_WEBSOCKET) {
                $factory = $this->container->get(DispatcherFactory::class);
                $factory
                    ->getRouter($port['name'])
                    ->addRoute('GET', '/socket.io/', SocketIO::class);
            }
        }
    }
}
