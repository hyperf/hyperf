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
use Hyperf\Server\Event\CoServerStart;
use Hyperf\Server\Event\CoServerStop;
use Hyperf\Server\Exception\RuntimeException;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;

class CoServer implements ServerInterface
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
     * @var Coroutine\Server
     */
    protected $server;

    /**
     * @var callable
     */
    protected $handler;

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
        run(function () {
            $this->initServer($this->config);

            $this->eventDispatcher->dispatch(new CoServerStart($this->server, $this->config->toArray()));

            CoordinatorManager::until(Constants::WORKER_START)->resume();

            $this->server->start();

            $this->eventDispatcher->dispatch(new CoServerStop($this->server));
        });
    }

    public function getServer()
    {
        return $this->server;
    }

    protected function initServer(ServerConfig $config)
    {
        $servers = $config->getServers();
        /** @var Port $server */
        $server = array_shift($servers);

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

        ServerManager::add($name, [$type, value(function () use ($host, $port) {
            $obj = new \stdClass();
            $obj->host = $host;
            $obj->port = $port;
            return $obj;
        })]);
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

    public static function isCoServer($server): bool
    {
        return $server instanceof Coroutine\Http\Server || $server instanceof Coroutine\Server;
    }
}
