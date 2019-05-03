<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\LoadBalancer;

abstract class AbstractLoadBalancer implements LoadBalancerInterface
{
    /**
     * @var Node[]
     */
    protected $nodes;

    /**
     * @param \Hyperf\LoadBalancer\Node[]|iterable $nodes
     */
    public function __construct(iterable $nodes = [])
    {
        $this->nodes = $nodes;
    }

    /**
     * @param \Hyperf\LoadBalancer\Node[]|iterable $nodes
     * @return $this
     */
    public function setNodes(iterable $nodes)
    {
        $this->nodes = $nodes;
        return $this;
    }
}
