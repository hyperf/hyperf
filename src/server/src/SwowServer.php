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
use Hyperf\Engine\Http\Server as HttpServer;
use Hyperf\Engine\Server as BaseServer;
use Hyperf\Server\Event\AllCoroutineServersClosed;
use Hyperf\Server\Event\CoroutineServerStart;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Server\Exception\RuntimeException;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swow\Buffer;
use Swow\Coroutine;
use Swow\Socket;

use function Swow\Sync\waitAll;

class SwowServer implements ServerInterface
{
    protected ?ServerConfig $config = null;

    protected ?Socket $server = null;

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
        $this->initServer($this->config);
        $servers = ServerManager::list();
        $config = $this->config->toArray();
        /**
         * @var HttpServer $server
         */
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

        waitAll();
    }

    public function getServer(): Socket
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
            $server->setSettings(array_replace($config->getSettings(), $server->getSettings()));

            $this->server = $this->makeServer($type, $host, $port, $server->getSettings());

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
                        $server->handle(function ($request, $session) use ($handler, $method) {
                            $this->waiter->wait(static function () use ($request, $session, $handler, $method) {
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
                    if ($server instanceof HttpServer) {
                        $server->handle(function ($request, $session) use ($handler, $method) {
                            $this->waiter->wait(static function () use ($request, $session, $handler, $method) {
                                $handler->{$method}($request, $session);
                            });
                        });
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
                    if ($server instanceof BaseServer) {
                        $server->handle(function (Socket $connection) use ($connectHandler, $connectMethod, $receiveHandler, $receiveMethod, $closeHandler, $closeMethod) {
                            if ($connectHandler && $connectMethod) {
                                $this->waiter->wait(static function () use ($connectHandler, $connectMethod, $connection) {
                                    $connectHandler->{$connectMethod}($connection, $connection->getId());
                                });
                            }
                            while (true) {
                                $byte = $connection->recv($buffer = new Buffer(Buffer::COMMON_SIZE));
                                if ($byte === 0) {
                                    if ($closeHandler && $closeMethod) {
                                        $this->waiter->wait(static function () use ($closeHandler, $closeMethod, $connection) {
                                            $closeHandler->{$closeMethod}($connection, $connection->getId());
                                        });
                                    }
                                    $connection->close();
                                    break;
                                }
                                // One coroutine at a time, consistent with other servers
                                $this->waiter->wait(static function () use ($receiveHandler, $receiveMethod, $connection, $buffer) {
                                    $receiveHandler->{$receiveMethod}($connection, $connection->getId(), 0, (string) $buffer);
                                });
                            }
                        });
                    }
                    if (isset($callbacks[Event::ON_PACKET])) {
                        [$receiveHandler, $receiveMethod] = $this->getCallbackMethod(Event::ON_PACKET, $callbacks);
                        if ($server instanceof BaseServer) {
                            $server->handle(function (Socket $connection, $data, $clientInfo) use ($receiveHandler, $receiveMethod) {
                                $receiveHandler->{$receiveMethod}($connection, $data, $clientInfo);
                            });
                        }
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

    protected function makeServer($type, $host, $port, $settings)
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
            case ServerInterface::SERVER_WEBSOCKET:
                $server = new HttpServer($this->logger);
                $this->initServerSettings($server, $settings);
                $server->bind($host, $port);
                return $server;
            case ServerInterface::SERVER_BASE:
                $server = new BaseServer($this->logger);
                $this->initServerSettings($server, $settings);
                $server->bind($host, $port);
                return $server;
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

    private function initServerSettings($server, $settings)
    {
        // Adapter Swoole server settings
        $mappings = [
            'open_tcp_nodelay' => 'tcp_nodelay',
            'buffer_output_size' => 'send_buffer_size',
            'output_buffer_size' => 'send_buffer_size',
            'buffer_input_size' => 'recv_buffer_size',
            'input_buffer_size' => 'recv_buffer_size',
        ];
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, $mappings)) {
                $key = $mappings[$key];
            }

            $method = Str::camel(sprintf('set_%s', $key));
            if (method_exists($server, $method)) {
                $server->{$method}($value);
            }
        }
    }
}
