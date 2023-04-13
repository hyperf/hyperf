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
namespace Hyperf\Stringable;

/**
 * Get a new stringable object from the given string.
 *
 * @param null|string $string
 * @return mixed|Stringable
 */
function str($string = null)
{
    if (func_num_args() === 0) {
        return new class() {
            public function __call($method, $parameters)
            {
                return Str::$method(...$parameters);
            }

            public function __toString()
            {
                return '';
            }
        };
    }

    return Str::of($string);
}
