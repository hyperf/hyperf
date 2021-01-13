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

use Hyperf\Contract;
use Psr\Container\ContainerInterface;

class PoolFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var KafkaPool[]
     */
    protected $pools = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name): KafkaPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        return $this->pools[$name] = $this->make($name);
    }

    protected function make(string $name): KafkaPool
    {
        if ($this->container instanceof Contract\ContainerInterface) {
            $pool = $this->container->make(KafkaPool::class, ['name' => $name]);
        } else {
            $pool = new KafkaPool($this->container, $name);
        }

        return $pool;
    }
}
