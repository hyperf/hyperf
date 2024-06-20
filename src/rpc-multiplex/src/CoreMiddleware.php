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

namespace Hyperf\RpcMultiplex;

use Closure;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\ErrorResponse;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\Response as RPCResponse;
use Hyperf\RpcMultiplex\Contract\HttpMessageBuilderInterface;
use Hyperf\RpcMultiplex\Exception\InvalidArgumentException;
use Hyperf\RpcMultiplex\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class CoreMiddleware extends \Hyperf\RpcServer\CoreMiddleware
{
    protected HttpMessageBuilderInterface $responseBuilder;

    protected DataFormatterInterface $dataFormatter;

    public function __construct(ContainerInterface $container, Protocol $protocol, HttpMessageBuilderInterface $builder, string $serverName)
    {
        parent::__construct($container, $protocol, $serverName);

        $this->responseBuilder = $builder;
        $this->dataFormatter = $protocol->getDataFormatter();
    }

    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): mixed
    {
        try {
            if ($dispatched->handler->callback instanceof Closure) {
                $callback = $dispatched->handler->callback;
                $response = $callback();
            } else {
                [$controller, $action] = $this->prepareHandler($dispatched->handler->callback);
                $controllerInstance = $this->container->get($controller);
                if (! method_exists($controller, $action)) {
                    throw new NotFoundException('The handler does not exists.');
                }

                try {
                    $parameters = $this->parseMethodParameters($controller, $action, $request->getParsedBody());
                } catch (\InvalidArgumentException) {
                    throw new InvalidArgumentException('The params is invalid.');
                }

                $response = $controllerInstance->{$action}(...$parameters);
            }
        } catch (NotFoundException $exception) {
            $data = $this->buildErrorData($request, 500, $exception->getMessage(), $exception);
            return $this->responseBuilder->buildResponse($request, $data);
        } catch (InvalidArgumentException $exception) {
            $data = $this->buildErrorData($request, 400, $exception->getMessage(), $exception);
            return $this->responseBuilder->buildResponse($request, $data);
        } catch (Throwable $exception) {
            $data = $this->buildErrorData($request, 500, $exception->getMessage(), $exception);
            $response = $this->responseBuilder->buildResponse($request, $data);
            $this->responseBuilder->persistToContext($response);
            throw $exception;
        }

        return $this->buildData($request, $response);
    }

    protected function handleNotFound(ServerRequestInterface $request): mixed
    {
        $data = $this->buildErrorData($request, 404, 'Not Found.');

        return $this->responseBuilder->buildResponse($request, $data);
    }

    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request): mixed
    {
        return $this->handleNotFound($request);
    }

    protected function transferToResponse($response, ServerRequestInterface $request): ResponsePlusInterface
    {
        return $this->responseBuilder->buildResponse($request, $response);
    }

    protected function buildErrorData(ServerRequestInterface $request, int $code, ?string $message = null, ?Throwable $throwable = null): array
    {
        $id = $request->getAttribute(Constant::REQUEST_ID);

        return $this->dataFormatter->formatErrorResponse(
            new ErrorResponse($id, $code, $message ?? Response::getReasonPhraseByCode($code), $throwable)
        );
    }

    protected function buildData(ServerRequestInterface $request, $response): array
    {
        $id = $request->getAttribute(Constant::REQUEST_ID);

        return $this->dataFormatter->formatResponse(new RPCResponse($id, $response));
    }
}
