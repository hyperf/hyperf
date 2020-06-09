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
namespace Hyperf\Server;

use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Server\Event\CoroutineServerStart;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\Server\Exception\RuntimeException;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;

class CoroutineServer implements ServerInterface
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
     * @var Coroutine\Http\Server|Coroutine\Server
     */
    protected $server;

    /**
     * @var callable
     */
    protected $handler;

    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
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
        run(function () {
            $this->initServer($this->config);
            $servers = ServerManager::list();
            foreach ($servers as $name => [$type, $server]) {
                Coroutine::create(function () use ($name, $server) {
                    $this->eventDispatcher->dispatch(new CoroutineServerStart($name, $server, $this->config->toArray()));
                    CoordinatorManager::until(Constants::WORKER_START)->resume();
                    $server->start();
                    $this->eventDispatcher->dispatch(new CoroutineServerStop($name, $server));
                });
            }
        });
    }

    /**
     * @return \Swoole\Coroutine\Http\Server|\Swoole\Coroutine\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    public static function isCoroutineServer($server): bool
    {
        return $server instanceof Coroutine\Http\Server || $server instanceof Coroutine\Server;
    }

    protected function initServer(ServerConfig $config): void
    {
        $servers = $config->getServers();
        foreach ($servers as $server) {
            $name = $server->getName();
            $type = $server->getType();
            $host = $server->getHost();
            $port = $server->getPort();
            $callbacks = array_replace($config->getCallbacks(), $server->getCallbacks());

            $this->server = $this->makeServer($type, $host, $port);
            $this->server->set(array_replace($config->getSettings(), $server->getSettings()));

            if (isset($callbacks[SwooleEvent::ON_REQUEST])) {
                [$class, $method] = $callbacks[SwooleEvent::ON_REQUEST];
                $handler = $this->container->get($class);
                if ($handler instanceof MiddlewareInitializerInterface) {
                    $handler->initCoreMiddleware($name);
                }
                $this->server->handle('/', [$handler, $method]);
            }

            ServerManager::add($name, [$type, $this->server]);
        }
    }

    protected function makeServer($type, $host, $port)
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
            case ServerInterface::SERVER_WEBSOCKET:
                return new Coroutine\Http\Server($host, $port);
            case ServerInterface::SERVER_BASE:
                return new Coroutine\Server($host, $port);
        }

        throw new RuntimeException('Server type is invalid.');
    }
}
