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

use Hyperf\Server\Entry\EventDispatcher;
use Hyperf\Server\Entry\Logger;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class ServerFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var null|LoggerInterface
     */
    protected $logger;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ServerInterface
     */
    protected $server;

    /**
     * @var null|ServerConfig
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function configure(array $config)
    {
        $this->config = new ServerConfig($config);

        $this->getServer()->init($this->config);
    }

    public function start()
    {
        return $this->getServer()->start();
    }

    public function getServer(): ServerInterface
    {
        if (! $this->server instanceof ServerInterface) {
            $serverName = $this->config->getType();
            $this->server = new $serverName(
                $this->container,
                $this->getLogger(),
                $this->getEventDispatcher()
            );
        }

        return $this->server;
    }

    public function setServer(Server $server): self
    {
        $this->server = $server;
        return $this;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        if ($this->eventDispatcher instanceof EventDispatcherInterface) {
            return $this->eventDispatcher;
        }
        return $this->getDefaultEventDispatcher();
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        if ($this->logger instanceof LoggerInterface) {
            return $this->logger;
        }
        return $this->getDefaultLogger();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    private function getDefaultEventDispatcher(): EventDispatcher
    {
        return new EventDispatcher();
    }

    private function getDefaultLogger(): Logger
    {
        return new Logger();
    }
}
