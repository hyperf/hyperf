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
namespace Hyperf\WebSocketServer;

use Hyperf\Context\Context;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpServer\CoreMiddleware as HttpCoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CoreMiddleware extends HttpCoreMiddleware
{
    public const HANDLER_NAME = 'class';

    /**
     * Handle the response when found.
     */
    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): ResponseInterface
    {
        [$controller,] = $this->prepareHandler($dispatched->handler->callback);
        if (! $this->container->has($controller)) {
            throw new WebSocketHandeShakeException('Router not exist.');
        }

        /** @var Response $response */
        $response = Context::get(ResponseInterface::class);

        $security = $this->container->get(Security::class);

        $key = $request->getHeaderLine(Security::SEC_WEBSOCKET_KEY);
        $response = $response->withStatus(101)->withHeaders($security->handshakeHeaders($key));
        if ($wsProtocol = $request->getHeaderLine(Security::SEC_WEBSOCKET_PROTOCOL)) {
            $response = $response->withHeader(Security::SEC_WEBSOCKET_PROTOCOL, $wsProtocol);
        }

        return $response->withAttribute(self::HANDLER_NAME, $controller);
    }
}
