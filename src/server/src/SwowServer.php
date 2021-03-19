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
use Hyperf\Engine\Http\Server as HttpServer;
use Hyperf\Server\Event\CoroutineServerStart;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Server\Exception\RuntimeException;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swow\Coroutine;

class SwowServer implements ServerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ServerConfig
     */
    protected $config;

    /**
     * @var HttpServer
     */
    protected $server;

    /**
     * @var bool
     */
    protected $mainServerStarted = false;

    public function __construct(ContainerInterface $container, LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->eventDispatcher = $dispatcher;
    }

    public function init(ServerConfig $config): ServerInterface
    {
        $this->config = $config;
        return $this;
    }

    public function start()
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
                }
                $this->eventDispatcher->dispatch(new CoroutineServerStart($name, $server, $config));
                CoordinatorManager::until(Constants::WORKER_START)->resume();
                $server->start();
                $this->eventDispatcher->dispatch(new CoroutineServerStop($name, $server));
                CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
            });
        }
    }

    public function getServer()
    {
        // TODO: Implement getServer() method.
    }

    protected function initServer(ServerConfig $config): void
    {
        $servers = $config->getServers();
        foreach ($servers as $server) {
            if (! $server instanceof \Hyperf\Server\Port) {
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
                        $server->handle([$handler, $method]);
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
