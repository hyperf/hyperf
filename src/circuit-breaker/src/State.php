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

namespace Hyperf\CircuitBreaker;

class State
{
    public const CLOSE = 0;

    public const HALF_OPEN = 1;

    public const OPEN = 2;

    protected int $state;

    public function __construct()
    {
        $this->state = self::CLOSE;
    }

    public function open()
    {
        $this->state = self::OPEN;
    }

    public function close()
    {
        $this->state = self::CLOSE;
    }

    public function halfOpen()
    {
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
}
