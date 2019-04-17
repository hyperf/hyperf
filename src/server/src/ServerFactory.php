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

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class ServerFactory
{
    use EntrySupport;

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
    protected $dispatcher;

    /**
     * @var ServerInterface
     */
    protected $server;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function configure($config)
    {
        $this->getServer()->init(new ServerConfig($config));
    }

    public function start()
    {
        return $this->getServer()->start();
    }

    /**
     * @return Server
     */
    public function getServer(): ServerInterface
    {
        if (! $this->server instanceof ServerInterface) {
            $this->server = new Server(
                $this->container,
                $this->getLogger(),
                $this->getDispatcher()
            );
        }

        return $this->server;
    }

    /**
     * @param Server $server
     * @return ServerFactory
     */
    public function setServer(Server $server): self
    {
        $this->server = $server;
        return $this;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->getEntryInstance('dispatcher');
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher): self
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->getEntryInstance('logger');
    }

    /**
     * @param LoggerInterface $logger
     * @return ServerFactory
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
}
