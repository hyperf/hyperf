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

namespace Hyperf\LoadBalancer;

class Node
{
    /**
     * @var int
     */
    public $weight = 0;

    public function __construct(int $weight)
    {
        $this->weight = $weight;
    }
}
