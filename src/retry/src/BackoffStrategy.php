<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\StrategyInterface;

class BackoffStrategy
{
    public function calculate(float $time): float
    {
        if ($time <= 0) {
            $time = 0.001;
        }
        $time *= 2;
        $jitter = mt_rand() / mt_getrandmax() + 0.5;
        $time = $time * $jitter;
        if ($time > 60) {
            $time = 60;
        }
        return $time;
    }
}
