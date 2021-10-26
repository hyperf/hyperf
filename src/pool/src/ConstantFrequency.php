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
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var null|int
     */
    protected $timerId;

    /**
     * @var int
     */
    protected $interval = 10000;

    public function __construct(?Pool $pool = null)
    {
        if ($pool) {
            $this->pool = $pool;
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
