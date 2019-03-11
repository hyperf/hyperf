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

namespace Hyperf\Pool;

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
        if (RequestContext::has($this->name)) {
            return RequestContext::get($this->name);
        }

        return null;
    }

    public function set(ConnectionInterface $connection): void
    {
        RequestContext::set($this->name, $connection);
    }
}
