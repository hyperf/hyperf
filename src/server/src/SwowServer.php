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
namespace Hyperf\Server;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Http\Server as HttpServer;
use Hyperf\Server\Event\AllCoroutineServersClosed;
use Hyperf\Server\Event\CoroutineServerStart;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Server\Exception\RuntimeException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swow\Coroutine;

class SwowServer implements ServerInterface
{
    protected ?ServerConfig $config = null;

    protected ?HttpServer $server = null;

    protected bool $mainServerStarted = false;

    public function __construct(
        protected ContainerInterface $container,
        protected LoggerInterface $logger,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function init(ServerConfig $config): ServerInterface
    {
        $this->config = $config;
        return $this;
    }

    public function start(): void
    {
        $this->writePid();
        $this->initServer($this->config);
        $servers = ServerManager::list();
        $config = $this->config->toArray();
        foreach ($servers as $name => [$type, $server]) {
            Coroutine::run(function () use ($name, $server, $config) {
                if (! $this->mainServerStarted) {
                    $this->mainServerStarted = true;
                    $this->eventDispatcher->dispatch(new MainCoroutineServerStart($name, $server, $config));
                    CoordinatorManager::until(Constants::WORKER_START)->resume();
                }
                $this->eventDispatcher->dispatch(new CoroutineServerStart($name, $server, $config));
                $server->start();
                $this->eventDispatcher->dispatch(new CoroutineServerStop($name, $server));
                CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
            });
        }

        if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
            $this->closeAll($servers);
        }
    }

    public function getServer(): HttpServer
    {
        return $this->server;
    }

    protected function closeAll(array $servers = []): void
    {
        /**
         * @var HttpServer $server
         */
        foreach ($servers as [$type, $server]) {
            $server->close();
        }

        $this->eventDispatcher->dispatch(new AllCoroutineServersClosed());
    }

    protected function initServer(ServerConfig $config): void
    {
        $servers = $config->getServers();
        foreach ($servers as $server) {
            if (! $server instanceof Port) {
                continue;
            }
            $name = $server->getName();
            $type = $server->getType();
            $host = $server->getHost();
            $port = $server->getPort();
            $callbacks = array_replace($config->getCallbacks(), $server->getCallbacks());

            $this->server = $this->makeServer($type, $host, $port);

            $this->bindServerCallbacks($this->server, $type, $name, $callbacks);

            ServerManager::add($name, [$type, $this->server, $callbacks]);
        }
    }

    protected function bindServerCallbacks($server, int $type, string $name, array $callbacks)
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
                if (isset($callbacks[Event::ON_REQUEST])) {
                    [$handler, $method] = $this->getCallbackMethod(Event::ON_REQUEST, $callbacks);
                    if ($handler instanceof MiddlewareInitializerInterface) {
                        $handler->initCoreMiddleware($name);
                    }
                    if ($server instanceof HttpServer) {
                        $server->handle(static function ($request, $session) use ($handler, $method) {
                            wait(static function () use ($request, $session, $handler, $method) {
                                $handler->{$method}($request, $session);
                            });
                        });
                    }
                }
                return;
            case ServerInterface::SERVER_WEBSOCKET:
                if (isset($callbacks[Event::ON_HAND_SHAKE])) {
                    [$handler, $method] = $this->getCallbackMethod(Event::ON_HAND_SHAKE, $callbacks);
                    if ($handler instanceof MiddlewareInitializerInterface) {
                        $handler->initCoreMiddleware($name);
                    }
                    if ($this->server instanceof \Swoole\Coroutine\Http\Server) {
                        $this->server->handle('/', [$handler, $method]);
                    }
                }
                return;
            case ServerInterface::SERVER_BASE:
                if (isset($callbacks[Event::ON_RECEIVE])) {
                    [$connectHandler, $connectMethod] = $this->getCallbackMethod(Event::ON_CONNECT, $callbacks);
                    [$receiveHandler, $receiveMethod] = $this->getCallbackMethod(Event::ON_RECEIVE, $callbacks);
                    [$closeHandler, $closeMethod] = $this->getCallbackMethod(Event::ON_CLOSE, $callbacks);
                    if ($receiveHandler instanceof MiddlewareInitializerInterface) {
                        $receiveHandler->initCoreMiddleware($name);
                    }
                    if ($this->server instanceof \Swoole\Coroutine\Server) {
                        $this->server->handle(function (Coroutine\Server\Connection $connection) use ($connectHandler, $connectMethod, $receiveHandler, $receiveMethod, $closeHandler, $closeMethod) {
                            if ($connectHandler && $connectMethod) {
                                parallel([static function () use ($connectHandler, $connectMethod, $connection) {
                                    $connectHandler->{$connectMethod}($connection, $connection->exportSocket()->fd);
                                }]);
                            }
                            while (true) {
                                $data = $connection->recv();
                                if (empty($data)) {
                                    if ($closeHandler && $closeMethod) {
                                        parallel([static function () use ($closeHandler, $closeMethod, $connection) {
                                            $closeHandler->{$closeMethod}($connection, $connection->exportSocket()->fd);
                                        }]);
                                    }
                                    $connection->close();
                                    break;
                                }
                                // One coroutine at a time, consistent with other servers
                                parallel([static function () use ($receiveHandler, $receiveMethod, $connection, $data) {
                                    $receiveHandler->{$receiveMethod}($connection, $connection->exportSocket()->fd, 0, $data);
                                }]);
                            }
                        });
                    }
                }
                return;
        }

        throw new RuntimeException('Server type is invalid or the server callback does not exists.');
    }

    protected function getCallbackMethod(string $callack, array $callbacks): array
    {
        $handler = $method = null;
        if (isset($callbacks[$callack])) {
            [$class, $method] = $callbacks[$callack];
            $handler = $this->container->get($class);
        }
        return [$handler, $method];
    }

    protected function makeServer($type, $host, $port)
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
                $server = new HttpServer($this->logger);
                $server->bind($host, $port);
                return $server;
            case ServerInterface::SERVER_WEBSOCKET:
                // return new Coroutine\Http\Server($host, $port, false, true);
            case ServerInterface::SERVER_BASE:
                // return new Coroutine\Server($host, $port, false, true);
        }

        throw new RuntimeException('Server type is invalid.');
    }

    private function writePid(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $file = $config->get('server.settings.pid_file', BASE_PATH . '/runtime/hyperf.pid');
        file_put_contents($file, getmypid());
    }
}
