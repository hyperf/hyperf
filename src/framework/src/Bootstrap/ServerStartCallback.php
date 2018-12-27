<?php

namespace Hyperflex\Framework\Bootstrap;


use Hyperflex\Memory;
use Hyperflex\Framework\Server;
use Psr\Container\ContainerInterface;
use Swoole\Server as SwooleServer;
use Hyperflex\Framework\Constants\SwooleEvent;

class ServerStartCallback
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Server
     */
    private $server;

    public function __construct(ContainerInterface $container, Server $server)
    {
        $this->container = $container;
        $this->server = $server;
    }

    public function onStart(SwooleServer $server)
    {
        Memory\LockManager::initialize(SwooleEvent::ON_WORKER_START, SWOOLE_RWLOCK);
        Memory\AtomicManager::initialize(SwooleEvent::ON_WORKER_START);
    }

}