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
namespace Hyperf\JsonRpc;

use Closure;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\Protocol;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CoreMiddleware extends \Hyperf\RpcServer\CoreMiddleware
{
    public function __construct(ContainerInterface $container, Protocol $protocol, protected ResponseBuilder $responseBuilder, string $serverName)
    {
        parent::__construct($container, $protocol, $serverName);
    }

    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): mixed
    {
        if ($dispatched->handler->callback instanceof Closure) {
            $response = call($dispatched->handler->callback);
        } else {
            [$controller, $action] = $this->prepareHandler($dispatched->handler->callback);
            $controllerInstance = $this->container->get($controller);
            if (! method_exists($controller, $action)) {
                // Route found, but the handler does not exist.
                return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::INTERNAL_ERROR);
            }

            try {
                $parameters = $this->parseMethodParameters($controller, $action, $request->getParsedBody());
            } catch (\InvalidArgumentException) {
                return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::INVALID_PARAMS);
            }

            try {
                $response = $controllerInstance->{$action}(...$parameters);
            } catch (\Throwable $exception) {
                $response = $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::SERVER_ERROR, $exception);
                $this->responseBuilder->persistToContext($response);

                throw $exception;
            }
        }
        return $response;
    }

    protected function handleNotFound(ServerRequestInterface $request): mixed
    {
        return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::METHOD_NOT_FOUND);
    }

    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request): mixed
    {
        return $this->handleNotFound($request);
    }

    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseBuilder->buildResponse($request, $response);
    }
}
