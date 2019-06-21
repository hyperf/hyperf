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

namespace Hyperf\WebSocketServer;

use FastRoute\Dispatcher;
use Hyperf\HttpServer\CoreMiddleware as HttpCoreMiddleware;
use Hyperf\Utils\Context;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CoreMiddleware extends HttpCoreMiddleware
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $uri = $request->getUri();
        /**
         * @var array
         *            Returns array with one of the following formats:
         *            [self::NOT_FOUND]
         *            [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
         *            [self::FOUND, $handler, ['varName' => 'value', ...]]
         */
        $routes = $this->dispatcher->dispatch($request->getMethod(), $uri->getPath());

        switch ($routes[0]) {
            case Dispatcher::NOT_FOUND:
                $response = $this->handleNotFound($request);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = $this->handleMethodNotAllowed($routes, $request);
                break;
            case Dispatcher::FOUND:
                $response = $this->handleFound($routes, $request);
                break;
        }
        if (! $response instanceof ResponseInterface) {
            $response = $this->transferToResponse($response, $request);
        }

        return $response->withAddedHeader('Server', 'Hyperf');
    }

    /**
     * Handle the response when found.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleFound(array $routes, ServerRequestInterface $request)
    {
        [$controller, $method] = $this->prepareHandler($routes[1]);
        if (! $this->container->has($controller)) {
            throw new WebSocketHandeShakeException('Router not exist.');
        }

        /** @var ResponseInterface $response */
        $response = Context::get(ResponseInterface::class);

        $security = $this->container->get(Security::class);

        $key = $request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
        $response = $response->withStatus(101)->withHeaders($security->handshakeHeaders($key));
        if ($wsProtocol = $request->getHeaderLine(Security::SEC_WEBSOCKET_PROTOCOL)) {
            $response = $response->withHeader(Security::SEC_WEBSOCKET_PROTOCOL, $wsProtocol);
        }

        return $response->withAttribute('class', $controller)->withAttribute('method', $method);
    }
}
