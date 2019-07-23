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
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\Server;
use Hyperf\Server\ServerManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server as SwooleServer;

class TcpServer extends Server
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
        LoggerInterface $logger,
        ProtocolManager $protocolManager
    ) {
        parent::__construct($serverName, $coreHandler, $container, $dispatcher, $logger);
        $this->protocolManager = $protocolManager;
        $protocolName = 'jsonrpc';
        $packerClass = $this->protocolManager->getPacker($protocolName);
        $this->packer = $this->container->get($packerClass);
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $container->get($this->protocolManager->getDataFormatter($protocolName)),
            'packer' => $this->packer,
        ]);
    }

    protected function buildResponse(int $fd, SwooleServer $server): ResponseInterface
    {
        $response = new Psr7Response();
        return $response->withAttribute('fd', $fd)->withAttribute('server', $server);
    }

    protected function buildRequest(int $fd, int $fromId, string $data): ServerRequestInterface
    {
        $class = $this->protocolManager->getPacker('jsonrpc');
        $packer = $this->container->get($class);
        $data = $this->packer->unpack($data);
        return $this->buildJsonRpcRequest($fd, $fromId, $data);
    }

    protected function buildJsonRpcRequest(int $fd, int $fromId, array $data)
    {
        if (! isset($data['method'])) {
            $data['method'] = '';
        }
        if (! isset($data['params'])) {
            $data['params'] = [];
        }
        /** @var \Swoole\Server\Port $port */
        [$type, $port] = ServerManager::get($this->serverName);

        $uri = (new Uri())->withPath($data['method'])->withHost($port->host)->withPort($port->port);
        $request = (new Psr7Request('POST', $uri))->withAttribute('fd', $fd)
            ->withAttribute('fromId', $fromId)
            ->withAttribute('data', $data)
            ->withAttribute('request_id', $data['id'] ?? null)
            ->withParsedBody($data['params'] ?? '');
        if (! isset($data['jsonrpc'])) {
            return $this->responseBuilder->buildErrorResponse($request, -32600);
        }
        return $request;
    }
}
