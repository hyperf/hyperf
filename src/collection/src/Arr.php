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

use ArgumentCountError;
use ArrayAccess;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;
use InvalidArgumentException;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * Most of the methods in this file come from illuminate/collections,
 * thanks Laravel Team provide such a useful class.
 */
class Arr
{
    use Macroable;

    /**
     * Determine whether the given value is array accessible.
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Add an element to an array using "dot" notation if it doesn't exist.
     */
    public static function add(array $array, string $key, mixed $value): array
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }
        return $array;
    }

    /**
     * Collapse an array of arrays into a single array.
     */
    public static function collapse(array $array): array
    {
        $results = [];
        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (! is_array($values)) {
                continue;
            }
            $results[] = $values;
        }
        return array_merge([], ...$results);
    }

    /**
     * Cross join the given arrays, returning all possible permutations.
     *
     * @param array ...$arrays
     */
    public static function crossJoin(...$arrays): array
    {
        $results = [[]];
        foreach ($arrays as $index => $array) {
            $append = [];
            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;
                    $append[] = $product;
                }
            }
            $results = $append;
        }
        return $results;
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     */
    public static function divide(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        return $results;
    }

    /**
     * Get all the given array except for a specified array of keys.
     */
    public static function except(array $array, array|int|string $keys): array
    {
        static::forget($array, $keys);
        return $array;
    }

    /**
     * Determine if the given key exists in the provided array.
     */
    public static function exists(array|ArrayAccess $array, int|string $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }

    /**
     * Return the first element in an array passing a given truth test.
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return value($default);
            }
            foreach ($array as $item) {
                return $item;
            }
        }
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }
        return value($default);
    }

    /**
     * Return the last element in an array passing a given truth test.
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? value($default) : end($array);
        }
        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     */
    public static function flatten(array $array, float|int $depth = INF): array
    {
        $result = [];
        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;
            if (! is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth <= 1
                    ? array_values($item)
                    : static::flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array|string $keys
     */
    public static function forget(array &$array, array|int|string $keys): void
    {
        $original = &$array;
        $keys = (array) $keys;
        if (count($keys) === 0) {
            return;
        }
        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);
                continue;
            }
            $parts = explode('.', (string) $key);
            // clean up before each pass
            $array = &$original;
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get an item from an array using "dot" notation.
     */
    public static function get(mixed $array, null|int|string $key = null, mixed $default = null)
    {
        if (! static::accessible($array)) {
            return value($default);
        }
        if (is_null($key)) {
            return $array;
        }
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        if (! is_string($key) || ! str_contains($key, '.')) {
            return $array[$key] ?? value($default);
        }
        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }
        return $array;
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param null|array|string $keys
     */
    public static function has(array|ArrayAccess $array, null|array|int|string $keys): bool
    {
        if (is_null($keys)) {
            return false;
        }
        $keys = (array) $keys;
        if (! $array) {
            return false;
        }
        if ($keys === []) {
            return false;
        }
        foreach ($keys as $key) {
            $subKeyArray = $array;
            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode('.', (string) $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Determine if any of the keys exist in an array using "dot" notation.
     */
    public static function hasAny(array|ArrayAccess $array, null|array|int|string $keys): bool
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (! $array) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (static::has($array, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if an array is associative.
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    /**
     * Determines if an array is a list.
     *
     * An array is a "list" if all array keys are sequential integers starting from 0 with no gaps in between.
     */
    public static function isList(array $array): bool
    {
        return array_is_list($array);
    }

    /**
     * Run an associative map over each of the items.
     *
     * The callback should return an associative array with a single key/value pair.
     *
     * @template TMapWithKeysKey of array-key
     * @template TMapWithKeysValue
     *
     * @param array<TKey, TValue> $array
     * @param callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue> $callback
     * @return array
     */
    public static function mapWithKeys(array $array, callable $callback)
    {
        $result = [];

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }

    /**
     * Get a subset of the items from the given array.
     */
    public static function only(array $array, array|int|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Pluck an array of values from an array.
     */
    public static function pluck(array $array, array|string $value, null|array|string $key = null): array
    {
        $results = [];
        [$value, $key] = static::explodePluckParameters($value, $key);
        foreach ($array as $item) {
            $itemValue = data_get($item, $value);
            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise, we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);
                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }
                $results[$itemKey] = $itemValue;
            }
        }
        return $results;
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param array<TKey, TValue> $array
     * @param null|TKey $key
     * @param TValue $value
     * @return array<TKey, TValue>
     */
    public static function prepend(array $array, mixed $value, null|int|string $key = null): array
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }
        return $array;
    }

    /**
     * Get a value from the array, and remove it.
     */
    public static function pull(array &$array, string $key, mixed $default = null): mixed
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);
        return $value;
    }

    /**
     * Get one or a specified number of random values from an array.
     *
     * @throws InvalidArgumentException
     */
    public static function random(array $array, ?int $number = null): mixed
    {
        $requested = is_null($number) ? 1 : $number;
        $count = count($array);
        if ($requested > $count) {
            throw new InvalidArgumentException("You requested {$requested} items, but there are only {$count} items available.");
        }
        if (is_null($number)) {
            return $array[array_rand($array)];
        }
        if ($number === 0) {
            return [];
        }
        $keys = array_rand($array, $number);
        $results = [];
        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }
        return $results;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     */
    public static function set(array &$array, null|int|string $key, mixed $value): array
    {
        if (is_null($key)) {
            return $array = $value;
        }
        if (! is_string($key)) {
            $array[$key] = $value;
            return $array;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * Shuffle the given array and return the result.
     */
    public static function shuffle(array $array, ?int $seed = null): array
    {
        if (empty($array)) {
            return [];
        }

        if (! is_null($seed)) {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
            return $array;
        }

        shuffle($array);

        return $array;
    }

    /**
     * Shuffle an associative array.
     */
    public static function shuffleAssoc(array $array, ?int $seed = null): array
    {
        if (empty($array)) {
            return [];
        }

        $keys = array_keys($array);
        $keys = static::shuffle($keys, $seed);
        $random = [];

        foreach ($keys as $key) {
            $random[$key] = $array[$key];
        }

        return $random;
    }

    /**
     * Sort the array using the given callback or "dot" notation.
     */
    public static function sort(array $array, null|callable|string $callback = null): array
    {
        return Collection::make($array)->sortBy($callback)->all();
    }

    /**
     * Recursively sort an array by keys and values.
     */
    public static function sortRecursive(array $array, int $options = SORT_REGULAR, bool $descending = false): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value, $options, $descending);
            }
        }
        if (static::isAssoc($array)) {
            $descending ? krsort($array, $options) : ksort($array, $options);
        } else {
            $descending ? rsort($array, $options) : sort($array, $options);
        }
        return $array;
    }

    /**
     * Convert the array into a query string.
     */
    public static function query(array $array): string
    {
        return http_build_query($array, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Filter the array using the given callback.
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     * @param mixed $value
     */
    public static function wrap($value): array
    {
        if (is_null($value)) {
            return [];
        }
        return ! is_array($value) ? [$value] : $value;
    }

    /**
     * Make array elements unique.
     */
    public static function unique(array $array): array
    {
        $result = [];
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $result[$key] = self::unique($item);
            } else {
                $result[$key] = $item;
            }
        }

        if (! self::isAssoc($result)) {
            return array_unique($result);
        }

        return $result;
    }

    public static function merge(array $array1, array $array2, bool $unique = true): array
    {
        $isAssoc = static::isAssoc($array1 ?: $array2);
        if ($isAssoc) {
            foreach ($array2 as $key => $value) {
                if (is_array($value)) {
                    $array1[$key] = static::merge($array1[$key] ?? [], $value, $unique);
                } else {
                    $array1[$key] = $value;
                }
            }
        } else {
            foreach ($array2 as $value) {
                if ($unique && in_array($value, $array1, true)) {
                    continue;
                }
                $array1[] = $value;
            }

            $array1 = array_values($array1);
        }
        return $array1;
    }

    /**
     * Remove one or more elements from an array.
     */
    public static function remove(array $array, mixed ...$value): array
    {
        $array = array_diff($array, $value);
        return array_values($array);
    }

    /**
     * Removes one or more elements from an array, keeping the original keys.
     */
    public static function removeKeepKey(array $array, mixed ...$value): array
    {
        foreach ($value as $item) {
            while (false !== ($index = array_search($item, $array))) {
                unset($array[$index]);
            }
        }

        return $array;
    }

    /**
     * Convert a flatten "dot" notation array into an expanded array.
     */
    public static function undot(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            static::set($result, $key, $value);
        }
        return $result;
    }

    /**
     * Conditionally compile classes from an array into a CSS class list.
     */
    public static function toCssClasses(array $array): string
    {
        $classList = static::wrap($array);

        $classes = [];

        foreach ($classList as $class => $constraint) {
            if (is_numeric($class)) {
                $classes[] = $constraint;
            } elseif ($constraint) {
                $classes[] = $class;
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Conditionally compile styles from an array into a style list.
     */
    public static function toCssStyles(array $array): string
    {
        $styleList = static::wrap($array);

        $styles = [];

        foreach ($styleList as $class => $constraint) {
            if (is_numeric($class)) {
                $styles[] = Str::finish($constraint, ';');
            } elseif ($constraint) {
                $styles[] = Str::finish($class, ';');
            }
        }

        return implode(' ', $styles);
    }

    /**
     * Join all items using a string. The final items can use a separate glue string.
     */
    public static function join(array $array, string $glue, string $finalGlue = ''): string
    {
        if ($finalGlue === '') {
            return implode($glue, $array);
        }

        if (count($array) === 0) {
            return '';
        }

        if (count($array) === 1) {
            return end($array);
        }

        $finalItem = array_pop($array);

        return implode($glue, $array) . $finalGlue . $finalItem;
    }

    /**
     * Key an associative array by a field or using a callback.
     */
    public static function keyBy(array $array, array|callable|string $keyBy): array
    {
        return Collection::make($array)->keyBy($keyBy)->all();
    }

    /**
     * Prepend the key names of an associative array.
     */
    public static function prependKeysWith(array $array, string $prependWith): array
    {
        return static::mapWithKeys($array, fn ($item, $key) => [$prependWith . $key => $item]);
    }

    /**
     * Select an array of values from an array.
     */
    public static function select(array $array, array|string $keys): array
    {
        $keys = static::wrap($keys);

        return static::map($array, static function ($item) use ($keys) {
            $result = [];

            foreach ($keys as $key) {
                if (Arr::accessible($item) && Arr::exists($item, $key)) {
                    $result[$key] = $item[$key];
                } elseif (is_object($item) && isset($item->{$key})) {
                    $result[$key] = $item->{$key};
                }
            }

            return $result;
        });
    }

    /**
     * Run a map over each nested chunk of items.
     *
     * @param array<TKey, array> $array
     * @param callable(mixed...): TValue $callback
     * @return array<TKey, TValue>
     */
    public static function mapSpread(array $array, callable $callback): array
    {
        return static::map($array, function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Run a map over each of the items in the array.
     */
    public static function map(array $array, callable $callback): array
    {
        $keys = array_keys($array);

        try {
            $items = array_map($callback, $array, $keys);
        } catch (ArgumentCountError) {
            $items = array_map($callback, $array);
        }

        return array_combine($keys, $items);
    }

    /**
     * Sort the array in descending order using the given callback or "dot" notation.
     */
    public static function sortDesc(array $array, null|array|callable|string $callback = null): array
    {
        return Collection::make($array)->sortByDesc($callback)->all();
    }

    /**
     * Recursively sort an array by keys and values in descending order.
     */
    public static function sortRecursiveDesc(array $array, int $options = SORT_REGULAR): array
    {
        return static::sortRecursive($array, $options, true);
    }

    /**
     * Filter items where the value is not null.
     */
    public static function whereNotNull(array $array): array
    {
        return static::where($array, static fn ($value) => ! is_null($value));
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     */
    protected static function explodePluckParameters(array|string $value, null|array|string $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;
        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);
        return [$value, $key];
    }
}
