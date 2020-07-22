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
namespace Hyperf\AsyncQueue;

use Hyperf\Utils\Context;

class Environment
{
    public function isAsyncQueue(): bool
    {
        return (bool) Context::get($this->getKey(), false);
    }

    public function setAsyncQueue(bool $asyncQueue): self
    {
        Context::set($this->getKey(), $asyncQueue);
        return $this;
    }

    protected function getKey(): string
    {
        return self::class . '::isAsyncQueue';
    }
}
