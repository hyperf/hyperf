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

namespace Hyperf\Framework;

use Hyperf\Contract\ProcessInterface;
use Hyperf\Contract\ServerOnRequestInterface;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Swoole\Server as SwooleServer;
use Swoole\Server\Port;

class Server
{
    /**
     * @var SwooleServer
     */
    protected $server;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $requests = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws \InvalidArgumentException when the server class not exist
     */
    public function initConfigs(array $serverConfigs): self
    {
        foreach ($serverConfigs as $i => $serverConfig) {
            $server = $serverConfig['server'];
            $constructor = $serverConfig['constructor'];
            $callbacks = $serverConfig['callbacks'];
            $settings = $serverConfig['settings'] ?? [];
            $processes = $serverConfig['processes'] ?? [];
            if (! class_exists($server)) {
                throw new InvalidArgumentException('Server not exist.');
            }
            if (! $this->server) {
                $serverName = $serverConfig['name'] ?? 'http';
                $this->server = new $server(...$constructor);
                $callbacks = array_replace($this->defaultCallbacks(), $callbacks);
                $this->registerSwooleEvents($this->server, $callbacks, $serverName);
                $this->server->set($settings);
            } else {
                $serverName = $serverConfig['name'] ?? 'http' . $i;
                $slaveServer = $this->server->addlistener(...$constructor);
                $this->registerSwooleEvents($slaveServer, $callbacks, $serverName);
            }
            // Trigger beforeStart event.
            if (isset($callbacks[SwooleEvent::ON_BEFORE_START])) {
                [$class, $method] = $callbacks[SwooleEvent::ON_BEFORE_START];
                $this->container->get($class)->$method();
            }

            foreach ($processes as $process) {
                $instance = $this->container->get($process);
                if ($instance instanceof ProcessInterface) {
                    $instance->bind($this->server);
                }
            }
        }

        return $this;
    }

    public function run()
    {
        $this->server->start();
    }

    /**
     * @param SwooleServer|Port $server
     */
    protected function registerSwooleEvents($server, array $events, string $serverName): void
    {
        foreach ($events as $event => $callback) {
            if (! SwooleEvent::isSwooleEvent($event)) {
                continue;
            }
            if (is_array($callback)) {
                if (array_key_exists($callback[0], $this->requests)) {
                    $logger = $this->container->get(StdoutLoggerInterface::class);

                    $logger->warning(sprintf(
                        'WARN: %s will be replaced by %s, please check your server.callbacks.request! ',
                        $this->requests[$callback[0]],
                        $serverName
                    ));
                }

                $this->requests[$callback[0]] = $serverName;
                $class = $this->container->get($callback[0]);
                if ('request' == $event) {
                    if (! $class instanceof ServerOnRequestInterface) {
                        throw new InvalidArgumentException(sprintf('%s is not instanceof %s', $callback[0], ServerOnRequestInterface::class));
                    }

                    $class->initCoreMiddleware($serverName);
                }
                $callback = [$class, $callback[1]];
            }
            $server->on($event, $callback);
        }
    }

    private function defaultCallbacks()
    {
        return [
            SwooleEvent::ON_WORKER_START => function (SwooleServer $server, int $workerId) {
                printf('Worker %d started.' . PHP_EOL, $workerId);
            },
        ];
    }
}
