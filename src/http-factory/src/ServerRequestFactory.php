<?php
declare(strict_types=1);
namespace Hyperf\HttpMessage\Factory;


use Hyperf\HttpMessage\Server\Request;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return (new Request($method, $uri))->withServerParams($serverParams);
    }
}