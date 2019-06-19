<?php

namespace Hyperf\Consul;


use Psr\Http\Message\RequestInterface;

class Utils
{

    public static function isHealthCheckRequest(RequestInterface $request): bool
    {
        return $request->getHeaderLine('user-agent') === 'Consul Health Check';
    }

}