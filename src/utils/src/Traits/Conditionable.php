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
namespace Hyperf\Utils\Traits;

use Closure;

/**
 * @deprecated since 3.1, use `Hyperf\Conditionable\Conditionable` instead.
 */
trait Conditionable
{
    /**
     * Apply the callback if the given "value" is truthy.
     *
     * @param mixed $value
     * @param callable $callback
     * @param null|callable $default
     *
     * @return $this|mixed
     */
    public function when($value, $callback, $default = null)
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if ($value) {
            return $callback($this, $value) ?? $this;
        }

        if ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }

    /**
     * Apply the callback if the given "value" is falsy.
     *
     * @param mixed $value
     * @param callable $callback
     * @param null|callable $default
     *
     * @return mixed
     */
    public function unless($value, $callback, $default = null)
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if (! $value) {
            return $callback($this, $value) ?: $this;
        }

        if ($default) {
            return $default($this, $value) ?: $this;
        }

        return $this;
    }
}
