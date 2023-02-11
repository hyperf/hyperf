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
namespace Hyperf\Swagger\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Hyperf\Swagger\Generator;
use Hyperf\Swagger\HttpServer;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class BootSwaggerListener implements ListenerInterface
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

    public function process(object $event): void
    {
        $config = $this->container->get(ConfigInterface::class);
        if (! $config->get('swagger.enable', false)) {
            return;
        }

        $port = $config->get('swagger.port', 9500);

        // Setup SwaggerUI Server
        $servers = $config->get('server.servers');
        foreach ($servers as $server) {
            if ($server['port'] == $port) {
                throw new InvalidArgumentException(sprintf('The swagger server port is invalid. Because it is conflicted with %s server.', $server['name']));
            }
        }

        $servers[] = [
            'name' => uniqid(),
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => $port,
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, 'onRequest'],
            ],
        ];

        $config->set('server.servers', $servers);

        if ($config->get('swagger.auto_generate', false)) {
            $this->container->get(Generator::class)->generate();
        }
    }
}
