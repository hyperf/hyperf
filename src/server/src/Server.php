<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Server;

use Hyperf\Contract\ServerOnRequestInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Framework\Event\BeforeServerStart;
use Hyperf\Framework\SwooleEvent;
use Hyperf\Server\Exception\InvalidArgumentException;
use Hyperf\Server\Exception\RuntimeException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server as SwooleServer;

class Server implements ServerInterface
{
    /**
     * @var bool
     */
    protected $http = false;

    /**
     * @var bool
     */
    protected $ws = false;

    /**
     * @var SwooleServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $requests = [];

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(ContainerInterface $container, StdoutLoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function init(ServerConfig $config): ServerInterface
    {
        $this->initServers($config);

        return $this;
    }

    public function start()
    {
        $this->server->start();
    }

    protected function initServers(ServerConfig $config)
    {
        $servers = $this->sortServers($config->getServers());

        foreach ($servers as $i => $server) {
            $type = $server['type'] ?? ServerInterface::SERVER_HTTP;
            $host = $server['host'] ?? '0.0.0.0';
            $port = $server['port'] ?? 9501;
            $sockType = $server['sock_type'] ?? SWOOLE_SOCK_TCP;
            $callbacks = $server['callbacks'] ?? [];

            if (! $this->server instanceof SwooleServer) {
                $name = $server['name'] ?? 'http';
                $this->server = $this->makeServer($type, $host, $port, $config->getMode(), $sockType);
                $callbacks = array_replace($this->defaultCallbacks(), $config->getCallbacks(), $callbacks);
                $this->registerSwooleEvents($this->server, $callbacks, $name);
                $this->server->set($config->getSettings());

                // Trigger BeforeMainEventStart event, this event only trigger once before main server start.
                $this->dispatcher->dispatch(new BeforeMainServerStart($this->server, $config->toArray()));
            } else {
                $name = $server['name'] ?? 'http' . $i;
                $slaveServer = $this->server->addlistener($host, $port, $sockType);
                $this->registerSwooleEvents($slaveServer, $callbacks, $name);
            }

            // Trigger beforeStart event.
            if (isset($callbacks[SwooleEvent::ON_BEFORE_START])) {
                [$class, $method] = $callbacks[SwooleEvent::ON_BEFORE_START];
                if ($this->container->has($class)) {
                    $this->container->get($class)->{$method}();
                }
            }

            // Trigger BeforeEventStart event.
            $this->dispatcher->dispatch(new BeforeServerStart($name));
        }
    }

    protected function sortServers(array $servers)
    {
        $sortServers = [];
        foreach ($servers as $server) {
            switch ($server['type'] ?? 0) {
                case ServerInterface::SERVER_HTTP:
                    $this->http = true;
                    if (! $this->ws) {
                        array_unshift($sortServers, $server);
                    } else {
                        $sortServers[] = $server;
                    }
                    break;
                case ServerInterface::SERVER_WS:
                    $this->ws = true;
                    array_unshift($sortServers, $server);
                    break;
                default:
                    $sortServers[] = $server;
                    break;
            }
        }

        return $sortServers;
    }

    protected function makeServer(int $type, string $host, int $port, int $mode, int $sockType)
    {
        switch ($type) {
            case ServerInterface::SERVER_HTTP:
                return new \Swoole\Http\Server($host, $port, $mode, $sockType);
            case ServerInterface::SERVER_WS:
                return new \Swoole\WebSocket\Server($host, $port, $mode, $sockType);
            case ServerInterface::SERVER_TCP:
                return new SwooleServer($host, $port, $mode, $sockType);
        }

        throw new RuntimeException('Server type is invalid.');
    }

    /**
     * @param Port|SwooleServer $server
     */
    protected function registerSwooleEvents($server, array $events, string $serverName): void
    {
        foreach ($events as $event => $callback) {
            if (! SwooleEvent::isSwooleEvent($event)) {
                continue;
            }
            if (is_array($callback)) {
                [$className, $method] = $callback;
                if (array_key_exists($className, $this->requests)) {
                    $this->logger->warning(sprintf('WARN: %s will be replaced by %s, please check your server.callbacks.request! ', $this->requests[$callback[0]], $serverName));
                }

                $this->requests[$className] = $serverName;
                $class = $this->container->get($className);
                if ($event == SwooleEvent::ON_REQUEST) {
                    if (! $class instanceof ServerOnRequestInterface) {
                        throw new InvalidArgumentException(sprintf('%s is not instanceof %s', $callback[0], ServerOnRequestInterface::class));
                    }

                    $class->initCoreMiddleware($serverName);
                }
                $callback = [$class, $method];
            }
            $server->on($event, $callback);
        }
    }

    protected function defaultCallbacks()
    {
        return [
            SwooleEvent::ON_WORKER_START => function (SwooleServer $server, int $workerId) {
                printf('Worker %d started.' . PHP_EOL, $workerId);
            },
        ];
    }
}
