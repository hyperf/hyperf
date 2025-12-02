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
use Hyperf\Collection\Collection;
use Hyperf\Contract\Arrayable;
use Hyperf\Support\Optional;

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     * @template TValue
     * @template TReturn
     *
     * @param (Closure():TReturn)|TValue $value
     * @param mixed ...$args
     * @return ($value is Closure ? TReturn : TValue)
     */
    function value(mixed $value, ...$args)
    {
        return \Hyperf\Support\value($value, ...$args);
    }
}
if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param null|mixed $default
     */
    function env($key, $default = null)
    {
        return \Hyperf\Support\env($key, $default);
    }
}
if (! function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @template TReturn
     *
     * @param float|int $times
     * @param callable(int):TReturn $callback
     * @param int $sleep millisecond
     * @return TReturn
     * @throws Throwable
     */
    function retry($times, callable $callback, int $sleep = 0)
    {
        return \Hyperf\Support\retry($times, $callback, $sleep);
    }
}
if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @template TValue
     * @template TReturn
     *
     * @param TValue $value
     * @param null|(callable(TValue):TReturn) $callback
     * @return ($callback is null ? TValue : TReturn)
     */
    function with($value, ?callable $callback = null)
    {
        return \Hyperf\Support\with($value, $callback);
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param null|Arrayable<TKey, TValue>|iterable<TKey, TValue> $value
     * @return Collection<TKey, TValue>
     */
    function collect($value = null)
    {
        return \Hyperf\Collection\collect($value);
    }
}

if (! function_exists('data_fill')) {
    /**
     * Fill in data where it's missing.
     *
     * @param mixed $target
     * @param array|string $key
     * @param mixed $value
     */
    function data_fill(&$target, $key, $value)
    {
        return \Hyperf\Collection\data_fill($target, $key, $value);
    }
}
if (! function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param null|array|int|string $key
     * @param null|mixed $default
     * @param mixed $target
     */
    function data_get($target, $key, $default = null)
    {
        return \Hyperf\Collection\data_get($target, $key, $default);
    }
}
if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed $target
     * @param array|string $key
     * @param bool $overwrite
     * @param mixed $value
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        return \Hyperf\Collection\data_set($target, $key, $value, $overwrite);
    }
}
if (! function_exists('head')) {
    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param array $array
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
     * @param array $array
     */
    function last($array)
    {
        return \Hyperf\Collection\last($array);
    }
}
if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     * @template TValue
     * @param TValue $value
     * @param null|callable $callback
     * @return ($callback is null ? HigherOrderTapProxy<TValue> : TValue)
     */
    function tap($value, $callback = null)
    {
        return \Hyperf\Tappable\tap($value, $callback);
    }
}

if (! function_exists('call')) {
    /**
     * Call a callback with the arguments.
     *
     * @param mixed $callback
     * @return null|mixed
     */
    function call($callback, array $args = [])
    {
        return \Hyperf\Support\call($callback, $args);
    }
}

if (! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param object|string $class
     * @return string
     */
    function class_basename($class)
    {
        return \Hyperf\Support\class_basename($class);
    }
}

if (! function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param object|string $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        return \Hyperf\Support\trait_uses_recursive($trait);
    }
}

if (! function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param object|string $class
     * @return array
     */
    function class_uses_recursive($class)
    {
        return \Hyperf\Support\class_uses_recursive($class);
    }
}

if (! function_exists('setter')) {
    /**
     * Create a setter string.
     */
    function setter(string $property): string
    {
        return \Hyperf\Support\setter($property);
    }
}

if (! function_exists('getter')) {
    /**
     * Create a getter string.
     */
    function getter(string $property): string
    {
        return \Hyperf\Support\getter($property);
    }
}

if (! function_exists('parallel')) {
    /**
     * @param callable[] $callables
     * @param int $concurrent if $concurrent is equal to 0, that means unlimited
     */
    function parallel(array $callables, int $concurrent = 0)
    {
        return Hyperf\Coroutine\parallel($callables, $concurrent);
    }
}

if (! function_exists('make')) {
    /**
     * Create an object instance, if the DI container exist in ApplicationContext,
     * then the object will be created by DI container via `make()` method, if not,
     * the object will create by `new` keyword.
     */
    function make(string $name, array $parameters = [])
    {
        return \Hyperf\Support\make($name, $parameters);
    }
}

if (! function_exists('run')) {
    /**
     * Run callable in non-coroutine environment, all hook functions by Swoole only available in the callable.
     *
     * @param array|callable $callbacks
     */
    function run($callbacks, int $flags = SWOOLE_HOOK_ALL): bool
    {
        return \Hyperf\Coroutine\run($callbacks, $flags);
    }
}

if (! function_exists('swoole_hook_flags')) {
    /**
     * Return the default swoole hook flags, you can rewrite it by defining `SWOOLE_HOOK_FLAGS`.
     */
    function swoole_hook_flags(): int
    {
        return \Hyperf\Support\swoole_hook_flags();
    }
}

if (! function_exists('optional')) {
    /**
     * Provide access to optional objects.
     * @template TValue
     * @template TReturn
     *
     * @param TValue $value
     * @param null|(callable(TValue):TReturn) $callback
     * @return ($callback is null ? Optional<TValue> : ($value is null ? null : TReturn))
     */
    function optional($value = null, ?callable $callback = null)
    {
        return \Hyperf\Support\optional($value, $callback);
    }
}

if (! function_exists('wait')) {
    /**
     * @template TReturn
     *
     * @param Closure():TReturn $closure
     * @return TReturn
     */
    function wait(Closure $closure, ?float $timeout = null)
    {
        return \Hyperf\Coroutine\wait($closure, $timeout);
    }
}

if (! function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null)
    {
        return \Hyperf\Translation\__($key, $replace, $locale);
    }
}

if (! function_exists('trans')) {
    function trans(string $key, array $replace = [], ?string $locale = null)
    {
        return \Hyperf\Translation\__($key, $replace, $locale);
    }
}

if (! function_exists('trans_choice')) {
    function trans_choice(string $key, $number, array $replace = [], ?string $locale = null): string
    {
        return \Hyperf\Translation\trans_choice($key, $number, $replace, $locale);
    }
}

if (! function_exists('config')) {
    /**
     * @param null|mixed $default
     */
    function config(string $key, $default = null)
    {
        return \Hyperf\Config\config($key, $default);
    }
}
