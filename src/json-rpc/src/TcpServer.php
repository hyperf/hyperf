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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Contract\CoreMiddlewareInterface;
use Hyperf\JsonRpc\Exception\Handler\TcpExceptionHandler;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Hyperf\RpcServer\Server;
use Hyperf\Server\Exception\InvalidArgumentException;
use Hyperf\Server\ServerManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function Hyperf\Support\make;

class TcpServer extends Server
{
    protected ?ResponseBuilder $responseBuilder = null;

    protected ?PackerInterface $packer = null;

    protected ProtocolManager $protocolManager;

    protected array $serverConfig = [];

    public function __construct(
        ContainerInterface $container,
        RequestDispatcher $dispatcher,
        ExceptionHandlerDispatcher $exceptionDispatcher,
        ProtocolManager $protocolManager,
        StdoutLoggerInterface $logger
    ) {
        parent::__construct($container, $dispatcher, $exceptionDispatcher, $logger);

        $this->protocolManager = $protocolManager;
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->initServerConfig($serverName);

        $this->initProtocol();

        parent::initCoreMiddleware($serverName);
    }

    protected function initProtocol()
    {
        $protocol = 'jsonrpc';
        if ($this->isLengthCheck()) {
            $protocol = 'jsonrpc-tcp-length-check';
        }

        $this->protocol = new Protocol($this->container, $this->protocolManager, $protocol, $this->serverConfig);
        $this->packer = $this->protocol->getPacker();
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $this->protocol->getDataFormatter(),
            'packer' => $this->packer,
        ]);
    }

    protected function isLengthCheck(): bool
    {
        return boolval($this->serverConfig['settings']['open_length_check'] ?? false);
    }

    protected function initServerConfig(string $serverName): array
    {
        $servers = $this->container->get(ConfigInterface::class)->get('server.servers', []);
        foreach ($servers as $server) {
            if ($server['name'] === $serverName) {
                return $this->serverConfig = $server;
            }
        }

        throw new InvalidArgumentException(sprintf('Server name %s is invalid.', $serverName));
    }

    protected function createCoreMiddleware(): CoreMiddlewareInterface
    {
        return new CoreMiddleware($this->container, $this->protocol, $this->responseBuilder, $this->serverName);
    }

    protected function buildResponse(int $fd, $server): ResponseInterface
    {
        $response = new Psr7Response();
        return $response->withAttribute('fd', $fd)->withAttribute('server', $server);
    }

    protected function buildRequest(int $fd, int $reactorId, string $data): ServerRequestInterface
    {
        return $this->buildJsonRpcRequest($fd, $reactorId, $this->packer->unpack($data) ?? ['jsonrpc' => '2.0']);
    }

    protected function buildJsonRpcRequest(int $fd, int $reactorId, array $data)
    {
        if (! isset($data['method'])) {
            $data['method'] = '';
        }
        if (! isset($data['params'])) {
            $data['params'] = [];
        }
        /** @var \Swoole\Server\Port $port */
        [, $port] = ServerManager::get($this->serverName);

        $uri = (new Uri())->withPath($data['method'])->withHost($port->host)->withPort($port->port);
        $request = (new Psr7Request('POST', $uri))->withAttribute('fd', $fd)
            ->withAttribute('fromId', $reactorId)
            ->withAttribute('data', $data)
            ->withAttribute('request_id', $data['id'] ?? null)
            ->withParsedBody($data['params']);

        $this->getContext()->setData($data['context'] ?? []);

        if (! isset($data['jsonrpc'])) {
            return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::INVALID_REQUEST);
        }
        return $request;
    }

    protected function getDefaultExceptionHandler(): array
    {
        return [
            TcpExceptionHandler::class,
        ];
    }
}
