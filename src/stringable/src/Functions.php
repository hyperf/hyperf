<?php

namespace Hyperf\Stringable;

/**
 * Get a new stringable object from the given string.
 *
 * @param  string|null  $string
 * @return Stringable|mixed
 */
function str($string = null)
{
    if (func_num_args() === 0) {
        return new class
        {
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