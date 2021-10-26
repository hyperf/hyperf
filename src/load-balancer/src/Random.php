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
namespace Hyperf\LoadBalancer;

class Random extends AbstractLoadBalancer
{
    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        if (empty($this->nodes)) {
            throw new \RuntimeException('Cannot select any node from load balancer.');
        }
        $key = array_rand($this->nodes);
        return $this->nodes[$key];
    }
}
