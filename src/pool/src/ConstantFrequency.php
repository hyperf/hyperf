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

namespace Hyperf\Pool;

use Hyperf\Coordinator\Timer;

class ConstantFrequency implements LowFrequencyInterface
{
    protected Timer $timer;

    protected ?int $timerId = null;

    protected int $interval = 10000;

    public function __construct(protected ?Pool $pool = null)
    {
        $this->timer = new Timer();
        if ($pool) {
            $this->timerId = $this->timer->tick(
                $this->interval / 1000,
                fn () => $this->pool->flushOne()
            );
        }
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function clear()
    {
        if ($this->timerId) {
            $this->timer->clear($this->timerId);
        }
        $this->timerId = null;
    }

    public function isLowFrequency(): bool
    {
        return false;
    }
}
