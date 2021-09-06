<?php

declare(strict_types=1);
namespace Hyperf\HttpMessage\Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                RequestFactoryInterface::class => RequestFactory::class,
                ResponseFactoryInterface::class => ResponseFactory::class,
                ServerRequestFactoryInterface::class => ServerRequestFactory::class,
                StreamFactoryInterface::class => StreamFactory::class,
//                UploadedFileFactoryInterface::class => UploadedFileFactory::class,
                UriFactoryInterface::class => UriFactory::class,
            ],
        ];
    }
}
