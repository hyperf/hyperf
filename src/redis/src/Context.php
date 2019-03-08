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

namespace Hyperf\Redis;

use Psr\Container\ContainerInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Context as RequestContext;

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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * Get a connection from request context.
     */
    public function connection(string $name): ?ConnectionInterface
    {
        $key = 'redis.' . $name;
        if (RequestContext::has($key)) {
            return RequestContext::get($key);
        }

        return null;
    }

    public function set($name, ConnectionInterface $connection): void
    {
        RequestContext::set('redis.' . $name, $connection);
    }
}
