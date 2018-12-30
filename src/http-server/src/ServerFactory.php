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

namespace Hyperf\HttpServer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;

    public function __invoke(ContainerInterface $container): Server
    {
        $config = $container->get(ConfigInterface::class);
        $middlewares = $config->get('middlewares.http', []);
        $exceptionHandlers = $config->get('exceptions.handler.http', [
            HttpExceptionHandler::class,
        ]);

        return new Server($middlewares, $this->coreMiddleware, $exceptionHandlers, $container);
    }
}
