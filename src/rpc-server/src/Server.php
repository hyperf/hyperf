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

namespace Hyperf\RpcServer;

use Hyperf\Context\RequestContext;
use Hyperf\Context\ResponseContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\DispatcherInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnReceiveInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\CoreMiddlewareInterface;
use Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\Rpc\Protocol;
use Hyperf\RpcServer\Event\RequestHandled;
use Hyperf\RpcServer\Event\RequestReceived;
use Hyperf\RpcServer\Event\RequestTerminated;
use Hyperf\Server\Option;
use Hyperf\Server\ServerFactory;
use Hyperf\Server\ServerManager;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Server\Connection;
use Swoole\Server as SwooleServer;
use Swow\Psr7\Message\ResponsePlusInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;
use Throwable;

use function Hyperf\Coroutine\defer;

abstract class Server implements OnReceiveInterface, MiddlewareInitializerInterface
{
    protected array $middlewares = [];

    protected array $exceptionHandlers = [];

    protected ?CoreMiddlewareInterface $coreMiddleware = null;

    protected ?string $serverName = null;

    protected ?Protocol $protocol = null;

    protected ?EventDispatcherInterface $event = null;

    protected ?Option $option = null;

    public function __construct(
        protected ContainerInterface $container,
        protected DispatcherInterface $dispatcher,
        protected ExceptionHandlerDispatcher $exceptionHandlerDispatcher,
        protected LoggerInterface $logger
    ) {
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->event = $this->container->get(EventDispatcherInterface::class);
        }
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = $this->createCoreMiddleware();

        $config = $this->container->get(ConfigInterface::class);
        $this->middlewares = $config->get('middlewares.' . $serverName, []);
        $this->exceptionHandlers = $config->get('exceptions.handler.' . $serverName, $this->getDefaultExceptionHandler());

        $this->initOption();
    }

    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        $request = $response = null;
        try {
            CoordinatorManager::until(Constants::WORKER_START)->yield();

            // Initialize PSR-7 Request and Response objects.
            RequestContext::set($request = $this->buildRequest($fd, $reactorId, $data));
            ResponseContext::set($response = $this->buildResponse($fd, $server));

            $request = $this->coreMiddleware->dispatch($request);
            $middlewares = $this->middlewares;

            $this->option?->isEnableRequestLifecycle() && $this->event?->dispatch(new RequestReceived(
                request: $request,
                response: $response,
                serverName: $this->serverName
            ));

            $response = $this->dispatcher->dispatch($request, $middlewares, $this->coreMiddleware);
        } catch (Throwable $throwable) {
            // Delegate the exception to exception handler.
            $exceptionHandlerDispatcher = $this->container->get(ExceptionHandlerDispatcher::class);
            $response = $exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        } finally {
            if (isset($request) && $this->option?->isEnableRequestLifecycle()) {
                defer(fn () => $this->event?->dispatch(new RequestTerminated(
                    request: $request,
                    response: $response ?? null,
                    exception: $throwable ?? null,
                    serverName: $this->serverName
                )));

                $this->event?->dispatch(new RequestHandled(
                    request: $request,
                    response: $response ?? null,
                    exception: $throwable ?? null,
                    serverName: $this->serverName
                ));
            }

            if (! $response instanceof ResponseInterface) {
                $response = $this->transferToResponse($response);
            }

            if ($response) {
                $this->send($server, $fd, $response);
            }
        }
    }

    public function onConnect($server, int $fd)
    {
        // $server is the main server object, not the server object that this callback on.
        /* @var \Swoole\Server\Port */
        [$type, $port] = ServerManager::get($this->serverName);
        $this->logger->debug(sprintf('Connect to %s:%d', $port->host, $port->port));
    }

    public function onClose($server, int $fd)
    {
        // $server is the main server object, not the server object that this callback on.
        /* @var \Swoole\Server\Port */
        [$type, $port] = ServerManager::get($this->serverName);
        $this->logger->debug(sprintf('Close on %s:%d', $port->host, $port->port));
    }

    protected function getDefaultExceptionHandler(): array
    {
        return [
            HttpExceptionHandler::class,
        ];
    }

    /**
     * @param Connection|SwooleServer $server
     */
    protected function send($server, int $fd, ResponseInterface $response): void
    {
        if ($server instanceof SwooleServer) {
            $server->send($fd, (string) $response->getBody());
        } elseif ($server instanceof Connection) {
            $server->send((string) $response->getBody());
        }
    }

    abstract protected function createCoreMiddleware(): CoreMiddlewareInterface;

    abstract protected function buildRequest(int $fd, int $reactorId, string $data): ServerRequestPlusInterface;

    abstract protected function buildResponse(int $fd, $server): ResponsePlusInterface;

    protected function transferToResponse($response): ?ResponseInterface
    {
        return ResponseContext::getOrNull()?->setBody(new SwooleStream($response));
    }

    protected function getContext()
    {
        return $this->container->get(RpcContext::class);
    }

    protected function initOption(): void
    {
        $ports = $this->container->get(ServerFactory::class)->getConfig()?->getServers();
        if (! $ports) {
            return;
        }

        foreach ($ports as $port) {
            if ($port->getName() === $this->serverName) {
                $this->option = $port->getOptions();
            }
        }

        $this->option ??= Option::make([]);
        $this->option->setMustSortMiddlewaresByMiddlewares($this->middlewares);
    }
}
