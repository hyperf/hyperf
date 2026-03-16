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

/**
 * Call the given Closure with the given value then return the value.
 * @template TValue
 * @param TValue $value
 * @param null|callable $callback
 * @return ($callback is null ? HigherOrderTapProxy<TValue> : TValue)
 */
function tap($value, $callback = null)
{
    if (is_null($callback)) {
        return new HigherOrderTapProxy($value);
    }

    $callback($value);

    return $value;
}
