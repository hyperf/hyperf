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
    protected ?LoggerInterface $logger = null;

    protected ?EventDispatcherInterface $eventDispatcher = null;

    protected ?ServerInterface $server = null;

    protected ?ServerConfig $config = null;

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function configure(array $config): void
    {
        $this->config = new ServerConfig($config);

        $this->getServer()->init($this->config);
    }

    public function start(): void
    {
        $this->getServer()->start();
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

    public function setServer(Server $server): static
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

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): static
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

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getConfig(): ?ServerConfig
    {
        return $this->config;
    }

    private function getDefaultEventDispatcher(): EventDispatcherInterface
    {
        return new EventDispatcher();
    }

    private function getDefaultLogger(): LoggerInterface
    {
        return new Logger();
    }
}
