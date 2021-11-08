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

use Swoole\Timer;

class ConstantFrequency implements LowFrequencyInterface
{
    protected ?int $timerId;

    protected int $interval = 10000;

    public function __construct(protected ?Pool $pool = null)
    {
        if ($pool) {
            $this->timerId = Timer::tick($this->interval, function () {
                $this->pool->flushOne();
            });
        }
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function clear()
    {
        if ($this->timerId) {
            Timer::clear($this->timerId);
        }
        $this->timerId = null;
    }

    public function isLowFrequency(): bool
    {
        return false;
    }
}
