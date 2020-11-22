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
namespace Hyperf\Kafka\Pool;

use Hyperf\Contract\ContainerInterface;

class PoolFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var KafkaConnectionPool[]
     */
    protected $pools = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name): KafkaConnectionPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        return $this->pools[$name] = $this->make($name);
    }

    protected function make(string $name): KafkaConnectionPool
    {
        if ($this->container instanceof ContainerInterface) {
            $pool = $this->container->make(KafkaConnectionPool::class, ['name' => $name]);
        } else {
            $pool = new KafkaConnectionPool($this->container, $name);
        }

        return $pool;
    }
}
