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

namespace Hyperf\Kafka;

use Hyperf\Context\Context as RequestContext;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;

class Context
{
    protected StdoutLoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * Get a connection from request context.
     */
    public function connection(string $name): ?ConnectionInterface
    {
        $connections = [];
        if (RequestContext::has('kafka')) {
            $connections = RequestContext::get('kafka');
        }

        if (isset($connections[$name]) && $connections[$name] instanceof ConnectionInterface) {
            return $connections[$name];
        }

        return null;
    }

    /**
     * @return ConnectionInterface[]
     */
    public function connections(): array
    {
        $connections = [];
        if (RequestContext::has('kafka')) {
            $connections = RequestContext::get('kafka');
        }

        return $connections;
    }

    public function set($name, ConnectionInterface $connection): void
    {
        $connections = $this->connections();
        $connections[$name] = $connection;
        RequestContext::set('kafka', $connections);
    }
}
