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
namespace Hyperf\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Context as CoroutineContext;
use Psr\Container\ContainerInterface;

class Context
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $name;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->name = $name;
    }

    /**
     * Get a connection from request context.
     */
    public function connection(): ?ConnectionInterface
    {
        if (CoroutineContext::has($this->name)) {
            return CoroutineContext::get($this->name);
        }

        return null;
    }

    public function set(ConnectionInterface $connection): void
    {
        CoroutineContext::set($this->name, $connection);
    }
}
