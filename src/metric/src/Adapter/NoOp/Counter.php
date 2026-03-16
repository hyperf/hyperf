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

namespace Hyperf\Metric\Adapter\NoOp;

use Hyperf\Metric\Contract\CounterInterface;

class Counter implements CounterInterface
{
    public function with(string ...$labelValues): static
    {
        return $this;
    }

    public function add(int $delta): void
    {
    }
}
