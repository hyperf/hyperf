<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\SocketIOServer\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Server\Server;
use Hyperf\SocketIOServer\SocketIO;

class AddRouteListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function listen(): array
    {
        return [BootApplication::class];
    }

    /**
     * @param BeforeMainServerStart $event
     */
    public function process(object $event)
    {
        $serverConfig = $this->container->get(ConfigInterface::class)->get('server.servers', []);
        foreach ($serverConfig as $port) {
            if ($port['type'] === Server::SERVER_WEBSOCKET) {
                $factory = $this->container->get(DispatcherFactory::class);
                $factory
                    ->getRouter($port['name'])
                    ->addRoute('GET', '/socket.io/', SocketIO::class);
            }
        }
    }
}
