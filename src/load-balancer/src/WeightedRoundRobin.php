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

use MathPHP\Algebra;
use RuntimeException;

class WeightedRoundRobin extends AbstractLoadBalancer
{
    /**
     * @var int
     */
    private $lastNode = 0;

    /**
     * @var int
     */
    private $currentWeight = 0;

    /**
     * @var int
     */
    private $maxWeight = 0;

    /**
     * Select an item via the load balancer.
     */
    public function select(array ...$parameters): Node
    {
        $count = count($this->nodes);
        if ($count <= 0) {
            throw new RuntimeException('Nodes missing.');
        }
        $this->maxWeight = $this->maxWeight($this->nodes);
        while (true) {
            $this->lastNode = ($this->lastNode + 1) % $count;
            if ($this->lastNode === 0) {
                $this->currentWeight = $this->currentWeight - $this->gcd($this->nodes);
                if ($this->currentWeight <= 0) {
                    $this->currentWeight = $this->maxWeight;
                    if ($this->currentWeight == 0) {
                        // Degrade to random algorithm.
                        return $this->nodes[array_rand($this->nodes)];
                    }
                }
            }
            /** @var Node $node */
            $node = $this->nodes[$this->lastNode];
            if ($node->weight >= $this->currentWeight) {
                return $node;
            }
        }
    }

    /**
     * Calculate the max weight of nodes.
     */
    private function maxWeight(iterable $nodes): int
    {
        $max = null;
        foreach ($nodes as $node) {
            if (! $node instanceof Node) {
                continue;
            }
            if ($max === null) {
                $max = $node->weight;
            } else {
                $max = max($max, $node->weight);
            }
        }
        return $max;
    }

    /**
     * Calculate the gcd of nodes.
     */
    private function gcd(iterable $nodes): int
    {
        $x = $y = null;
        foreach ($nodes as $node) {
            if (! $node instanceof Node) {
                continue;
            }
            if ($x === null) {
                $x = $node->weight;
                continue;
            }
            $y = $node->weight;
            $x = Algebra::gcd($x, $y);
        }
        return $x;
    }
}
