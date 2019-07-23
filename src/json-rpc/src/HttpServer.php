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

namespace Hyperf\JsonRpc;

use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpServer\Server;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\Server\Exception\InvalidArgumentException;
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
     * @var ProtocolManager
     */
    protected $protocolManager;

    /**
     * @var \Hyperf\Rpc\Contract\PackerInterface
     */
    protected $packer;

    /**
     * @var \Hyperf\JsonRpc\ResponseBuilder
     */
    protected $responseBuilder;

    public function __construct(
        string $serverName,
        string $coreHandler,
        ContainerInterface $container,
        $dispatcher,
        ProtocolManager $protocolManager
    ) {
        parent::__construct($serverName, $coreHandler, $container, $dispatcher);
        $this->protocolManager = $protocolManager;
        $protocolName = 'jsonrpc-http';
        $packerClass = $this->protocolManager->getPacker($protocolName);
        $this->packer = $this->container->get($packerClass);
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $container->get($this->protocolManager->getDataFormatter($protocolName)),
            'packer' => $this->packer,
        ]);
    }

    protected function initRequestAndResponse(SwooleRequest $request, SwooleResponse $response): array
    {
        // Initialize PSR-7 Request and Response objects.
        $psr7Request = Psr7Request::loadFromSwooleRequest($request);
        if (! $this->isHealthCheck($psr7Request)) {
            if (strpos($psr7Request->getHeaderLine('content-type'), 'application/json') === false) {
                $this->responseBuilder->buildErrorResponse($request, -32700);
            }
            $content = $this->packer->unpack($psr7Request->getBody()->getContents());
            if (! isset($content['jsonrpc'], $content['method'], $content['params'])) {
                $this->responseBuilder->buildErrorResponse($request, -32600);
            }
        }
        $psr7Request = $psr7Request->withUri($psr7Request->getUri()->withPath($content['method'] ?? '/'))
            ->withParsedBody($content['params'] ?? null)
            ->withAttribute('data', $content ?? []);
        Context::set(ServerRequestInterface::class, $psr7Request);
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response($response));
        return [$psr7Request, $psr7Response];
    }

    protected function isHealthCheck(RequestInterface $request)
    {
        return $request->getHeaderLine('user-agent') === 'Consul Health Check';
    }
}
