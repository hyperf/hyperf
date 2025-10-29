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

namespace Hyperf\Tappable;

trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param null|callable $callback
     * @return ($callback is null ? HigherOrderTapProxy : $this)
     */
    public function tap($callback = null)
    {
        return tap($this, $callback);
    }
}
