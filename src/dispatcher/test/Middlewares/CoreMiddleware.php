<?php

namespace HyperflexTest\Dispatcher\Middlewares;


use Hyperflex\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CoreMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = Context::get(ResponseInterface::class);
        $response = $response->withAddedHeader('Server', 'Hyperflex');
        return $response;
    }
}