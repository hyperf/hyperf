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
        $factory = $this->container->get(PoolFactory::class);
        $pool = $factory->getPool($this->name);

        $connection = $pool->get()->getConnection();

        return $connection->{$name}(...$arguments);
    }
}
