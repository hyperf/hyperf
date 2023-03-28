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
namespace Hyperf\Collection;

use Closure;

/**
 * Create a collection from the given value.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @param null|\Hyperf\Contract\Arrayable<TKey, TValue>|iterable<TKey, TValue> $value
 * @return Collection<TKey, TValue>
 */
function collect($value = []): Collection
{
    return new Collection($value);
}

/**
 * Fill in data where it's missing.
 *
 * @param mixed $target
 * @param array|string $key
 * @param mixed $value
 * @return mixed
 */
function data_fill(&$target, $key, $value)
{
    return data_set($target, $key, $value, false);
}

/**
 * Get an item from an array or object using "dot" notation.
 *
 * @param mixed $target
 * @param null|array|int|string $key
 * @param mixed $default
 * @return mixed
 */
function data_get($target, $key, $default = null)
{
    if (is_null($key)) {
        return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    foreach ($key as $i => $segment) {
        unset($key[$i]);

        if (is_null($segment)) {
            return $target;
        }

        if ($segment === '*') {
            if ($target instanceof Collection) {
                $target = $target->all();
            } elseif (! is_iterable($target)) {
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

/**
 * Set an item on an array or object using dot notation.
 *
 * @param mixed $target
 * @param array|string $key
 * @param mixed $value
 * @param bool $overwrite
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
            /* @phpstan-ignore-next-line */
            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite) {
            $target[$segment] = $value;
        }
    }

    return $target;
}

/**
 * Get the first element of an array. Useful for method chaining.
 *
 * @param array $array
 * @return mixed
 */
function head($array)
{
    return reset($array);
}

/**
 * Get the last element from an array.
 *
 * @param array $array
 * @return mixed
 */
function last($array)
{
    return end($array);
}

/**
 * Return the default value of the given value.
 *
 * @param mixed ...$args
 * @return mixed
 */
function value(mixed $value, ...$args)
{
    return $value instanceof Closure ? $value(...$args) : $value;
}
