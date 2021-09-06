<?php
declare(strict_types=1);
namespace Hyperf\HttpMessage\Factory;


use Hyperf\HttpMessage\Uri\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = ''): UriInterface
    {
        return (new Uri($uri));
    }
}