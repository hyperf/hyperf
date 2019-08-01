<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use Hyperf\Utils\Serializer\SymfonyNormalizer;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;

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
                NormalizerInterface::class => class_exists(Serializer::class)
                    ? SymfonyNormalizer::class : SimpleNormalizer::class,
                Serializer::class => SerializerFactory::class,
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
