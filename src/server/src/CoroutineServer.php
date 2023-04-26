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
use Hyperf\Coroutine\Waiter;
use Hyperf\Engine\SafeSocket;
use Hyperf\Server\Event\AllCoroutineServersClosed;
use Hyperf\Server\Event\CoroutineServerStart;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Server\Exception\RuntimeException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Coroutine\Server;

use function Hyperf\Coroutine\run;
use function Hyperf\Support\swoole_hook_flags;

class CoroutineServer implements ServerInterface
{
    protected ?ServerConfig $config = null;

    protected HttpServer|Server|null $server = null;

    /**
     * @var callable
     */
    protected $handler;

    protected bool $mainServerStarted = false;

    private Waiter $waiter;

    public function __construct(
        protected ContainerInterface $container,
        protected LoggerInterface $logger,
        protected EventDispatcherInterface $eventDispatcher
    ) {
        $this->waiter = new Waiter(-1);
    }

    public function init(ServerConfig $config): ServerInterface
    {
        $this->config = $config;
        return $this;
    }

    public function start(): void
    {
        $this->writePid();
        run(function () {
            $this->initServer($this->config);
            $servers = ServerManager::list();
            $config = $this->config->toArray();
            foreach ($servers as $name => [$type, $server]) {
                Coroutine::create(function () use ($name, $server, $config) {
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
        }, swoole_hook_flags());
    }

    public function getServer(): HttpServer|Server
    {
        return $this->server;
    }

    protected function closeAll(array $servers = []): void
    {
        /**
         * @var HttpServer|Server $server
         */
        foreach ($servers as [$type, $server]) {
            $server->shutdown();
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
            $settings = array_replace($config->getSettings(), $server->getSettings());
            $this->server->set($settings);

            $this->bindServerCallbacks($type, $name, $callbacks, $server);

            ServerManager::add($name, [$type, $this->server, $callbacks]);
        }
    }

    protected function bindServerCallbacks(int $type, string $name, array $callbacks, Port $port)
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
                if (isset($callbacks[Event::ON_REQUEST])) {
                    [$handler, $method] = $this->getCallbackMethod(Event::ON_REQUEST, $callbacks);
                    if ($handler instanceof MiddlewareInitializerInterface) {
                        $handler->initCoreMiddleware($name);
                    }
                    if ($this->server instanceof HttpServer) {
                        $this->server->handle('/', static function ($request, $response) use ($handler, $method) {
                            Coroutine::create(static function () use ($request, $response, $handler, $method) {
                                $handler->{$method}($request, $response);
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
                    if ($this->server instanceof HttpServer) {
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
                    if ($this->server instanceof Server) {
                        $this->server->handle(function (Server\Connection $connection) use ($connectHandler, $connectMethod, $receiveHandler, $receiveMethod, $closeHandler, $closeMethod, $port) {
                            $socket = $connection->exportSocket();
                            $fd = $socket->fd;
                            $options = $port->getOptions();
                            if ($options && $options->getSendChannelCapacity() > 0) {
                                $socket = new SafeSocket($socket, $options->getSendChannelCapacity(), false);
                                $connection = new Connection($socket);
                            }

                            if ($connectHandler && $connectMethod) {
                                $this->waiter->wait(static function () use ($connectHandler, $connectMethod, $connection, $fd) {
                                    $connectHandler->{$connectMethod}($connection, $fd);
                                });
                            }
                            while (true) {
                                $data = $socket->recvPacket();
                                if (empty($data)) {
                                    if ($closeHandler && $closeMethod) {
                                        $this->waiter->wait(static function () use ($closeHandler, $closeMethod, $connection, $fd) {
                                            $closeHandler->{$closeMethod}($connection, $fd);
                                        });
                                    }
                                    $socket->close();
                                    break;
                                }
                                // One coroutine at a time, consistent with other servers
                                $this->waiter->wait(static function () use ($receiveHandler, $receiveMethod, $connection, $data, $fd) {
                                    $receiveHandler->{$receiveMethod}($connection, $fd, 0, $data);
                                });
                            }
                        });
                    }
                }
                return;
        }

        throw new RuntimeException('Server type is invalid or the server callback does not exists.');
    }

    protected function getCallbackMethod(string $callback, array $callbacks): array
    {
        $handler = $method = null;
        if (isset($callbacks[$callback])) {
            [$class, $method] = $callbacks[$callback];
            $handler = $this->container->get($class);
        }
        return [$handler, $method];
    }

    protected function makeServer($type, $host, $port)
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
            case ServerInterface::SERVER_WEBSOCKET:
                return new HttpServer($host, $port, false, true);
            case ServerInterface::SERVER_BASE:
                return new Server($host, $port, false, true);
        }

        throw new RuntimeException('Server type is invalid.');
    }

    private function writePid(): void
    {
        $config = $this->container->get(ConfigInterface::class);
        $file = $config->get('server.settings.pid_file');
        if ($file) {
            file_put_contents($file, getmypid());
        }
    }
}
