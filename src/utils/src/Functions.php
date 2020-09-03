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
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Backoff;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\HigherOrderTapProxy;
use Hyperf\Utils\Optional;
use Hyperf\Utils\Parallel;
use Hyperf\Utils\Str;

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
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
     * @param string $key
     * @param null|mixed $default
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
     * @param float|int $times
     * @param int $sleep millisecond
     * @throws \Throwable
     */
    function retry($times, callable $callback, int $sleep = 0)
    {
        $backoff = new Backoff($sleep);
        beginning:
        try {
            return $callback();
        } catch (\Throwable $e) {
            if (--$times < 0) {
                throw $e;
            }
            $backoff->sleep();
            goto beginning;
        }
    }
}
if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param mixed $value
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param null|mixed $value
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
     * @param mixed $target
     * @param array|string $key
     * @param mixed $value
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
     * @param null|array|int|string $key
     * @param null|mixed $default
     * @param mixed $target
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', is_int($key) ? (string) $key : $key);
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
     * @param mixed $target
     * @param array|string $key
     * @param bool $overwrite
     * @param mixed $value
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
                $target[$segment] = [];
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
        return end($array);
    }
}
if (! function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value.
     *
     * @param null|callable $callback
     * @param mixed $value
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
     * @return null|mixed
     */
    function call($callback, array $args = [])
    {
        $result = null;
        if ($callback instanceof \Closure) {
            $result = $callback(...$args);
        } elseif (is_object($callback) || (is_string($callback) && function_exists($callback))) {
            $result = $callback(...$args);
        } elseif (is_array($callback)) {
            [$object, $method] = $callback;
            $result = is_object($object) ? $object->{$method}(...$args) : $object::$method(...$args);
        } else {
            $result = call_user_func_array($callback, $args);
        }
        return $result;
    }
}

if (! function_exists('go')) {
    /**
     * @return bool|int
     */
    function go(callable $callable)
    {
        $id = Coroutine::create($callable);
        return $id > 0 ? $id : false;
    }
}

if (! function_exists('co')) {
    /**
     * @return bool|int
     */
    function co(callable $callable)
    {
        $id = Coroutine::create($callable);
        return $id > 0 ? $id : false;
    }
}

if (! function_exists('defer')) {
    function defer(callable $callable): void
    {
        Coroutine::defer($callable);
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
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (! function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param string $trait
     * @return array
     */
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
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
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (! function_exists('setter')) {
    /**
     * Create a setter string.
     */
    function setter(string $property): string
    {
        return 'set' . Str::studly($property);
    }
}

if (! function_exists('getter')) {
    /**
     * Create a getter string.
     */
    function getter(string $property): string
    {
        return 'get' . Str::studly($property);
    }
}

if (! function_exists('parallel')) {
    /**
     * @param callable[] $callables
     * @param int $concurrent if $concurrent is equal to 0, that means unlimit
     */
    function parallel(array $callables, int $concurrent = 0)
    {
        $parallel = new Parallel($concurrent);
        foreach ($callables as $key => $callable) {
            $parallel->add($callable, $key);
        }
        return $parallel->wait();
    }
}

if (! function_exists('make')) {
    /**
     * Create a object instance, if the DI container exist in ApplicationContext,
     * then the object will be create by DI container via `make()` method, if not,
     * the object will create by `new` keyword.
     */
    function make(string $name, array $parameters = [])
    {
        if (ApplicationContext::hasContainer()) {
            $container = ApplicationContext::getContainer();
            if (method_exists($container, 'make')) {
                return $container->make($name, $parameters);
            }
        }
        $parameters = array_values($parameters);
        return new $name(...$parameters);
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
        if (Coroutine::inCoroutine()) {
            throw new RuntimeException('Function \'run\' only execute in non-coroutine environment.');
        }

        \Swoole\Runtime::enableCoroutine(true, $flags);

        $result = \Swoole\Coroutine\Run(...(array) $callbacks);

        \Swoole\Runtime::enableCoroutine(false);
        return $result;
    }
}

if (! function_exists('swoole_hook_flags')) {
    /**
     * Return the default swoole hook flags, you can rewrite it by defining `SWOOLE_HOOK_FLAGS`.
     */
    function swoole_hook_flags(): int
    {
        return defined('SWOOLE_HOOK_FLAGS') ? SWOOLE_HOOK_FLAGS : SWOOLE_HOOK_ALL;
    }
}

if (! function_exists('optional')) {
    /**
     * Provide access to optional objects.
     *
     * @param mixed $value
     * @return mixed
     */
    function optional($value = null, callable $callback = null)
    {
        if (is_null($callback)) {
            return new Optional($value);
        }
        if (! is_null($value)) {
            return $callback($value);
        }
    }
}
