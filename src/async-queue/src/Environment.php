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

namespace Hyperf\AsyncQueue;

class Environment
{
    /**
     * @var bool
     */
    protected $asyncQueue = false;

    public function isAsyncQueue(): bool
    {
        return $this->asyncQueue;
    }

    public function setAsyncQueue(bool $asyncQueue): self
    {
        $this->asyncQueue = $asyncQueue;
        return $this;
    }
}
