<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\JsonRpc;

use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\Contract\CoreMiddlewareInterface;
use Hyperf\HttpServer\Server;
use Hyperf\JsonRpc\Exception\Handler\HttpExceptionHandler;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class HttpServer extends Server
{
    /**
     * @var Protocol
     */
    protected $protocol;

    /**
     * @var \Hyperf\Contract\PackerInterface
     */
    protected $packer;

    /**
     * @var \Hyperf\JsonRpc\ResponseBuilder
     */
    protected $responseBuilder;

    public function __construct(
        ContainerInterface $container,
        RequestDispatcher $dispatcher,
        ExceptionHandlerDispatcher $exceptionHandlerDispatcher,
        ProtocolManager $protocolManager
    ) {
        parent::__construct($container, $dispatcher, $exceptionHandlerDispatcher);
        $this->protocol = new Protocol($container, $protocolManager, 'jsonrpc-http');
        $this->packer = $this->protocol->getPacker();
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $this->protocol->getDataFormatter(),
            'packer' => $this->packer,
        ]);
    }

    protected function getDefaultExceptionHandler(): array
    {
        return [
            HttpExceptionHandler::class,
        ];
    }

    protected function createCoreMiddleware(): CoreMiddlewareInterface
    {
        return new HttpCoreMiddleware($this->container, $this->protocol, $this->serverName);
    }

    protected function initRequestAndResponse(SwooleRequest $request, SwooleResponse $response): array
    {
        // Initialize PSR-7 Request and Response objects.
        $psr7Request = Psr7Request::loadFromSwooleRequest($request);
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response($response));
        if (! $this->isHealthCheck($psr7Request)) {
            if (strpos($psr7Request->getHeaderLine('content-type'), 'application/json') === false) {
                $psr7Response = $this->responseBuilder->buildErrorResponse($psr7Request, ResponseBuilder::PARSE_ERROR);
            }
            // @TODO Optimize the error handling of encode.
            $content = $this->packer->unpack($psr7Request->getBody()->getContents());
            if (! isset($content['jsonrpc'], $content['method'], $content['params'])) {
                $psr7Response = $this->responseBuilder->buildErrorResponse($psr7Request, ResponseBuilder::INVALID_REQUEST);
            }
        }
        $psr7Request = $psr7Request->withUri($psr7Request->getUri()->withPath($content['method'] ?? '/'))
            ->withParsedBody($content['params'] ?? null)
            ->withAttribute('data', $content ?? [])
            ->withAttribute('request_id', $content['id'] ?? null);
        Context::set(ServerRequestInterface::class, $psr7Request);
        Context::set(ResponseInterface::class, $psr7Response);
        return [$psr7Request, $psr7Response];
    }

    protected function isHealthCheck(RequestInterface $request): bool
    {
        return $request->getHeaderLine('user-agent') === 'Consul Health Check';
    }
}
