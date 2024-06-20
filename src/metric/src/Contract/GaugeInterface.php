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

namespace Hyperf\Metric\Contract;

/**
 * Gauge describes a metric that takes specific values over time.
 * An example of a gauge is the current depth of a job queue.
 */
interface GaugeInterface
{
    public function with(string ...$labelValues): static;

    public function set(float $value): void;

    public function add(float $delta): void;
}
