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

use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}
if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }
}
if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param  int $times
     * @param  callable $callback
     * @param  int $sleep
     * @return mixed
     * @throws \Exception
     */
    function retry($times, callable $callback, $sleep = 0)
    {
        $times--;
        beginning:
        try {
            return $callback();
        } catch (\Exception $e) {
            if (! $times) {
                throw $e;
            }
            $times--;
            if ($sleep) {
                usleep($sleep * 1000);
            }
            goto beginning;
        }
    }
}
if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param  mixed $value
     * @param  callable|null $callback
     * @return mixed
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

/**
 * Array helpers
 */
if (! function_exists('array_add')) {
    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $value
     * @return array
     */
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}
if (! function_exists('array_collapse')) {
    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  array $array
     * @return array
     */
    function array_collapse($array)
    {
        return Arr::collapse($array);
    }
}
if (! function_exists('array_divide')) {
    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array $array
     * @return array
     */
    function array_divide($array)
    {
        return Arr::divide($array);
    }
}
if (! function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array $array
     * @param  string $prepend
     * @return array
     */
    function array_dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }
}
if (! function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array $array
     * @param  array|string $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}
if (! function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array $array
     * @param  callable|null $callback
     * @param  mixed $default
     * @return mixed
     */
    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}
if (! function_exists('array_flatten')) {
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array $array
     * @param  int $depth
     * @return array
     */
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}
if (! function_exists('array_forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array $array
     * @param  array|string $keys
     * @return void
     */
    function array_forget(&$array, $keys)
    {
        return Arr::forget($array, $keys);
    }
}
if (! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}
if (! function_exists('array_has')) {
    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param  \ArrayAccess|array $array
     * @param  string|array $keys
     * @return bool
     */
    function array_has($array, $keys)
    {
        return Arr::has($array, $keys);
    }
}
if (! function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array $array
     * @param  callable|null $callback
     * @param  mixed $default
     * @return mixed
     */
    function array_last($array, callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}
if (! function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     *
     * @param  array $array
     * @param  array|string $keys
     * @return array
     */
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}
if (! function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param  array $array
     * @param  string|array $value
     * @param  string|array|null $key
     * @return array
     */
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}
if (! function_exists('array_prepend')) {
    /**
     * Push an item onto the beginning of an array.
     *
     * @param  array $array
     * @param  mixed $value
     * @param  mixed $key
     * @return array
     */
    function array_prepend($array, $value, $key = null)
    {
        return Arr::prepend($array, $value, $key);
    }
}
if (! function_exists('array_pull')) {
    /**
     * Get a value from the array, and remove it.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}
if (! function_exists('array_random')) {
    /**
     * Get a random value from an array.
     *
     * @param  array $array
     * @param  int|null $num
     * @return mixed
     */
    function array_random($array, $num = null)
    {
        return Arr::random($array, $num);
    }
}
if (! function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $value
     * @return array
     */
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}
if (! function_exists('array_sort')) {
    /**
     * Sort the array by the given callback or attribute name.
     *
     * @param  array $array
     * @param  callable|string|null $callback
     * @return array
     */
    function array_sort($array, $callback = null)
    {
        return Arr::sort($array, $callback);
    }
}
if (! function_exists('array_sort_recursive')) {
    /**
     * Recursively sort an array by keys and values.
     *
     * @param  array $array
     * @return array
     */
    function array_sort_recursive($array)
    {
        return Arr::sortRecursive($array);
    }
}
if (! function_exists('array_where')) {
    /**
     * Filter the array using the given callback.
     *
     * @param  array $array
     * @param  callable $callback
     * @return array
     */
    function array_where($array, callable $callback)
    {
        return Arr::where($array, $callback);
    }
}
if (! function_exists('array_wrap')) {
    /**
     * If the given value is not an array, wrap it in one.
     *
     * @param  mixed $value
     * @return array
     */
    function array_wrap($value)
    {
        return Arr::wrap($value);
    }
}
if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed $value
     * @return Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}
if (! function_exists('data_fill')) {
    /**
     * Fill in data where it's missing.
     *
     * @param  mixed $target
     * @param  string|array $key
     * @param  mixed $value
     * @return mixed
     */
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}
if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed $target
     * @param  string|array $key
     * @param  mixed $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        $key = is_array($key) ? $key : explode('.', $key);
        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return value($default);
                }
                $result = [];
                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }
                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }
            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }
        return $target;
    }
}
if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed $target
     * @param  string|array $key
     * @param  mixed $value
     * @param  bool $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);
        if (($segment = array_shift($segments)) === '*') {
            if (! Arr::accessible($target)) {
                $target = [];
            }
            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (! Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }
                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];
            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }
        return $target;
    }
}
if (! function_exists('head')) {
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array $array
     * @return mixed
     */
    function head($array)
    {
        return reset($array);
    }
}
if (! function_exists('last')) {
    /**
     * Get the last element from an array.
     *
     * @param  array $array
     * @return mixed
     */
    function last($array)
    {
        return end($array);
    }
}
if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param  mixed $value
     * @param  callable|null $callback
     * @return mixed
     */
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }
        $callback($value);
        return $value;
    }
}

if (! function_exists('call')) {
    /**
     * Call a callback with the arguments.
     *
     * @param mixed $callback
     * @param array $args
     * @return mixed|null
     */
    function call($callback, array $args = [])
    {
        $result = null;
        if (is_object($callback) || (is_string($callback) && function_exists($callback))) {
            $result = $callback(...$args);
        } elseif (is_array($callback)) {
            [$object, $method] = $callback;
            $result = is_object($object) ? $object->$method(...$args) : $object::$method(...$args);
        } else {
            $result = call_user_func_array($callback, $args);
        }
        return $result;
    }
}

if (! function_exists('go')) {
    /**
     * @param callable $callback
     */
    function go($callback)
    {
        \Hyperf\Utils\Coroutine::create($callback);
    }
}

if (! function_exists('defer')) {
    /**
     * @param callable $callback
     */
    function defer($callback): void
    {
        \Hyperf\Utils\Coroutine::defer($callback);
    }
}
