<?php

namespace Hyperf\Framework;


use Hyperf\Framework\Constants\SwooleEvent;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function initConfigs(array $serverConfigs): self
    {
        foreach ($serverConfigs as $serverConfig) {
            $server = $serverConfig['server'];
            $constructor = $serverConfig['constructor'];
            $callbacks = $serverConfig['callbacks'];
            $settings = $serverConfig['settings'] ?? [];
            if (! class_exists($server)) {
                throw new \InvalidArgumentException('Server not exist.');
            }
            if (! $this->server) {
                $this->server = new $server(...$constructor);
                $callbacks = array_replace($this->defaultCallbacks(), $callbacks);
                $this->registerSwooleEvents($this->server, $callbacks);
                $this->server->set($settings);
            } else {
                $slaveServer = $this->server->addlistener(...$constructor);
                $this->registerSwooleEvents($slaveServer, $callbacks);
            }
        }
        return $this;
    }

    /**
     * @param SwooleServer|Port $server
     * @param array $events
     */
    protected function registerSwooleEvents($server, array $events): void
    {
        foreach ($events as $event => $callback) {
            if (is_array($callback)) {
                $callback = [$this->container->get($callback[0]), $callback[1]];
            }
            $server->on($event, $callback);
        }
    }

    public function run()
    {
        $this->server->start();
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