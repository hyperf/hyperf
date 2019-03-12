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

namespace Hyperf\Breaker;

class State
{
    const CLOSE = 0;

    const HALF_OPEN = 1;

    const OPEN = 2;

    protected $state;

    protected $time;

    protected $fallbackCount;

    protected $callCount;

    public function __construct()
    {
        $this->state = self::CLOSE;
        $this->time = microtime(true);
        $this->fallbackCount = 0;
        $this->callCount = 0;
    }

    public function open()
    {
        $this->time = microtime(true);
        $this->fallbackCount = 0;
        $this->callCount = 0;
        $this->state = self::OPEN;
    }

    public function close()
    {
        $this->time = microtime(true);
        $this->fallbackCount = 0;
        $this->callCount = 0;
        $this->state = self::CLOSE;
    }

    public function halfOpen()
    {
        $this->time = microtime(true);
        $this->state = self::HALF_OPEN;
    }

    public function isOpen(): bool
    {
        return $this->state === self::OPEN;
    }

    public function isClose(): bool
    {
        return $this->state === self::CLOSE;
    }

    public function isHalfOpen(): bool
    {
        return $this->state === self::HALF_OPEN;
    }

    public function addFallbackCount()
    {
        $time = microtime(true) - $this->time;
        if ($time > 10) {
            $this->close();
            return;
        }

        if (++$this->fallbackCount > 100) {
            $this->close();
        }
    }

    public function addCallCount()
    {
        ++$this->callCount;
    }

    public function should()
    {
    }
}
