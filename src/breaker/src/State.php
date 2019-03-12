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
