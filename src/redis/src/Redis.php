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

use Hyperf\Redis\Pool\PoolFactory;
use Psr\Container\ContainerInterface;

class Redis
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $name = 'default';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __call($name, $arguments)
    {
        $context = $this->container->get(Context::class);
        $connection = $context->connection($this->name);
        if (! $connection) {
            $factory = $this->container->get(PoolFactory::class);
            $pool = $factory->getPool($this->name);

            $connection = $pool->get();
            $context->set($this->name, $connection);
        }

        return $connection->getConnection()->{$name}(...$arguments);
    }
}
