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

use Hyperf\Context\Context as CoroutineContext;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Context
{
    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container, protected string $name)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
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
