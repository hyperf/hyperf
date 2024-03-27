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

use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Framework\Bootstrap;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Framework\Event\BeforeServerStart;
use Hyperf\Server\Exception\RuntimeException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;
use Swoole\Server\Port as SwoolePort;
use Swoole\WebSocket\Server as SwooleWebSocketServer;

class Server implements ServerInterface
{
    protected bool $enableHttpServer = false;

    protected bool $enableWebsocketServer = false;

    protected ?SwooleServer $server = null;

    protected array $onRequestCallbacks = [];

    public function __construct(protected ContainerInterface $container, protected LoggerInterface $logger, protected EventDispatcherInterface $eventDispatcher)
    {
    }

    public function init(ServerConfig $config): ServerInterface
    {
        $this->initServers($config);

        return $this;
    }

    public function start(): void
    {
        $this->server->start();
    }

    public function getServer(): SwooleServer
    {
        return $this->server;
    }

    protected function initServers(ServerConfig $config)
    {
        $servers = $this->sortServers($config->getServers());

        foreach ($servers as $server) {
            $name = $server->getName();
            $type = $server->getType();
            $host = $server->getHost();
            $port = $server->getPort();
            $sockType = $server->getSockType();
            $callbacks = $server->getCallbacks();

            if (! $this->server instanceof SwooleServer) {
                $this->server = $this->makeServer($type, $host, $port, $config->getMode(), $sockType);
                $callbacks = array_replace($this->defaultCallbacks(), $config->getCallbacks(), $callbacks);
                $this->registerSwooleEvents($this->server, $callbacks, $name);
                $this->server->set(array_replace($config->getSettings(), $server->getSettings()));
                ServerManager::add($name, [$type, current($this->server->ports)]);

                if (class_exists(BeforeMainServerStart::class)) {
                    // Trigger BeforeMainServerStart event, this event only trigger once before main server start.
                    $this->eventDispatcher->dispatch(new BeforeMainServerStart($this->server, $config->toArray()));
                }
            } else {
                /** @var bool|SwoolePort $slaveServer */
                $slaveServer = $this->server->addlistener($host, $port, $sockType);
                if (! $slaveServer) {
                    throw new \RuntimeException("Failed to listen server port [{$host}:{$port}]");
                }
                $server->getSettings() && $slaveServer->set(array_replace($config->getSettings(), $server->getSettings()));
                $this->registerSwooleEvents($slaveServer, $callbacks, $name);
                ServerManager::add($name, [$type, $slaveServer]);
            }

            // Trigger beforeStart event.
            if (isset($callbacks[Event::ON_BEFORE_START])) {
                [$class, $method] = $callbacks[Event::ON_BEFORE_START];
                if ($this->container->has($class)) {
                    $this->container->get($class)->{$method}();
                }
            }

            if (class_exists(BeforeServerStart::class)) {
                // Trigger BeforeServerStart event.
                $this->eventDispatcher->dispatch(new BeforeServerStart($name));
            }
        }
    }

    /**
     * @param Port[] $servers
     * @return Port[]
     */
    protected function sortServers(array $servers): array
    {
        $sortServers = [];
        foreach ($servers as $server) {
            switch ($server->getType()) {
                case ServerInterface::SERVER_HTTP:
                    $this->enableHttpServer = true;
                    if (! $this->enableWebsocketServer) {
                        array_unshift($sortServers, $server);
                    } else {
                        $sortServers[] = $server;
                    }
                    break;
                case ServerInterface::SERVER_WEBSOCKET:
                    $this->enableWebsocketServer = true;
                    array_unshift($sortServers, $server);
                    break;
                default:
                    $sortServers[] = $server;
                    break;
            }
        }

        return $sortServers;
    }

    protected function makeServer(int $type, string $host, int $port, int $mode, int $sockType): SwooleServer
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
                return new SwooleHttpServer($host, $port, $mode, $sockType);
            case ServerInterface::SERVER_WEBSOCKET:
                return new SwooleWebSocketServer($host, $port, $mode, $sockType);
            case ServerInterface::SERVER_BASE:
                return new SwooleServer($host, $port, $mode, $sockType);
        }

        throw new RuntimeException('Server type is invalid.');
    }

    protected function registerSwooleEvents(SwoolePort|SwooleServer $server, array $events, string $serverName): void
    {
        foreach ($events as $event => $callback) {
            if (! Event::isSwooleEvent($event)) {
                continue;
            }
            if (is_array($callback)) {
                [$className, $method] = $callback;
                if (array_key_exists($className . $method, $this->onRequestCallbacks)) {
                    $this->logger->warning(sprintf('%s will be replaced by %s. Each server should have its own onRequest callback. Please check your configs.', $this->onRequestCallbacks[$className . $method], $serverName));
                }

                $this->onRequestCallbacks[$className . $method] = $serverName;
                $class = $this->container->get($className);
                if (method_exists($class, 'setServerName')) {
                    // Override the server name.
                    $class->setServerName($serverName);
                }
                if ($class instanceof MiddlewareInitializerInterface) {
                    $class->initCoreMiddleware($serverName);
                }
                $callback = [$class, $method];
            }
            $server->on($event, $callback);
        }
    }

    protected function defaultCallbacks()
    {
        $hasCallback = class_exists(Bootstrap\StartCallback::class)
            && class_exists(Bootstrap\ManagerStartCallback::class)
            && class_exists(Bootstrap\WorkerStartCallback::class);

        if ($hasCallback) {
            $callbacks = [
                Event::ON_MANAGER_START => [Bootstrap\ManagerStartCallback::class, 'onManagerStart'],
                Event::ON_WORKER_START => [Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
                Event::ON_WORKER_STOP => [Bootstrap\WorkerStopCallback::class, 'onWorkerStop'],
                Event::ON_WORKER_EXIT => [Bootstrap\WorkerExitCallback::class, 'onWorkerExit'],
            ];
            if ($this->server->mode === SWOOLE_BASE) {
                return $callbacks;
            }

            return array_merge([
                Event::ON_START => [Bootstrap\StartCallback::class, 'onStart'],
            ], $callbacks);
        }

        return [
            Event::ON_WORKER_START => function (SwooleServer $server, int $workerId) {
                printf('Worker %d started.' . PHP_EOL, $workerId);
            },
        ];
    }
}
