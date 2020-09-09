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
namespace Hyperf\Amqp\Pool;

use Hyperf\Amqp\RpcConnection;
use Hyperf\Contract;
use Psr\Container\ContainerInterface;

class PoolFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AmqpConnectionPool[]
     */
    protected $pools = [];

    /**
     * @var AmqpConnectionPool[]
     */
    protected $rpcPools = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name): AmqpConnectionPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        return $this->pools[$name] = $this->make($name);
    }

    public function getRpcPool(string $name): AmqpConnectionPool
    {
        if (isset($this->rpcPools[$name])) {
            return $this->rpcPools[$name];
        }

        return $this->rpcPools[$name] = $this->make($name)->setClass(RpcConnection::class);
    }

    protected function make(string $name): AmqpConnectionPool
    {
        if ($this->container instanceof Contract\ContainerInterface) {
            $pool = $this->container->make(AmqpConnectionPool::class, ['name' => $name]);
        } else {
            $pool = new AmqpConnectionPool($this->container, $name);
        }

        return $pool;
    }
}
