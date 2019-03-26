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

use Hyperf\HttpServer\Command\StartServer;
use Hyperf\HttpServer\Command\StartServerFactory;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Psr\Http\Message\ServerRequestInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Server::class => ServerFactory::class,
                RequestInterface::class => Request::class,
                ServerRequestInterface::class => Request::class,
                ResponseInterface::class => Response::class,
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
