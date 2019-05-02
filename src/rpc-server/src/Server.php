<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcServer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\DispatcherInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnReceiveInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Framework\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Hyperf\HttpServer\Exception\ServerException;
use Hyperf\Rpc\Contract\EofInterface;
use Hyperf\Rpc\Contract\ResponseInterface;
use Hyperf\Rpc\Response as Psr7Response;
use Hyperf\Server\ServerManager;
use Hyperf\Utils\Context;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server as SwooleServer;
use Throwable;

class Server implements OnReceiveInterface, MiddlewareInitializerInterface
{
    /**
     * @var array
     */
    private $middlewares;

    /**
     * @var string
     */
    private $coreHandler;

    /**
     * @var MiddlewareInterface
     */
    private $coreMiddleware;

    /**
     * @var array
     */
    private $exceptionHandlers;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $serverName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PackerInterface
     */
    private $packer;

    public function __construct(
        string $serverName,
        string $coreHandler,
        ContainerInterface $container,
        $dispatcher,
        PackerInterface $packer,
        LoggerInterface $logger
    ) {
        $this->serverName = $serverName;
        $this->coreHandler = $coreHandler;
        $this->container = $container;
        $this->dispatcher = $dispatcher;
        $this->packer = $packer;
        $this->logger = $logger;
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $coreHandler = $this->coreHandler;
        $this->coreMiddleware = new $coreHandler($this->container, $serverName);

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, [
            HttpExceptionHandler::class,
        ]);
    }

    public function onReceive(SwooleServer $server, int $fd, int $fromId, string $data): void
    {
        try {
            // Initialize PSR-7 Request and Response objects.
            Context::set(ServerRequestInterface::class, $request = $this->buildRequest($fd, $fromId, $data));
            Context::set(\Psr\Http\Message\ResponseInterface::class, $this->buildResponse($fd, $server));

            // $middlewares = array_merge($this->middlewares, MiddlewareManager::get());
            $middlewares = $this->middlewares;

            $response = $this->dispatcher->dispatch($request, $middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            echo '<pre>';
            var_dump($throwable->getMessage());
            echo '</pre>';
            exit();
            if (! $throwable instanceof ServerException) {
                $message = sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile());
                $this->logger->error($message);
            }
            // Delegate the exception to exception handler.
            $exceptionHandlerDispatcher = $this->container->get(ExceptionHandlerDispatcher::class);
            $response = $exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            if (! $response instanceof ResponseInterface) {
                $response = Context::get(ResponseInterface::class);
            }
            // Send the Response to client.
            $response->send();
        }
    }

    public function onConnect(SwooleServer $server)
    {
        // $server is the main server object, not the server object that this callback on.
        /*
         * @var \Swoole\Server\Port
         */
        [$type, $port] = ServerManager::get($this->serverName);
        $this->logger->debug(sprintf('Connect to %s:%d', $port->host, $port->port));
    }

    private function buildRequest(int $fd, int $fromId, string $data): ServerRequestInterface
    {
        $data = $this->packer->unpack($data);
        if (isset($data['jsonrpc'])) {
            return $this->buildJsonRpcRequest($fd, $fromId, $data);
        }
        throw new InvalidArgumentException('Doesn\'t match any protocol.');
    }

    private function buildResponse(int $fd, SwooleServer $server): ResponseInterface
    {
        $response = new Psr7Response($fd, $server);
        if ($response instanceof EofInterface) {
            $eof = value(function () use ($server) {
                /** @var \Swoole\Server\Port $port */
                [$type, $port] = ServerManager::get($this->serverName);
                if (isset($port->setting['package_eof'])) {
                    return $port->setting['package_eof'];
                }
                if (isset($server->setting['package_eof'])) {
                    return $server->setting['package_eof'];
                }
                return "\r\n";
            });
            $response->setEof($eof);
        }
        return $response;
    }

    private function buildJsonRpcRequest(int $fd, int $fromId, string $data)
    {
        if (! isset($data['method'])) {
            $data['method'] = '';
        }
        if (! isset($data['params'])) {
            $data['params'] = [];
        }
        /** @var \Swoole\Server\Port $port */
        [$type, $port] = ServerManager::get($this->serverName);

        $uri = (new Uri())
            ->withPath($data['method'])
            ->withScheme('jsonrpc')
            ->withHost($port->host)
            ->withPort($port->port);
        return (new Psr7Request('GET', $uri))
            ->withAttribute('fd', $fd)
            ->withAttribute('fromId', $fromId)
            ->withAttribute('data', $data)
            ->withProtocolVersion($data['jsonrpc'] ?? '2.0')
            ->withParsedBody($data['params']);
    }
}
