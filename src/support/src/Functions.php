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
namespace Hyperf\Support;

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Context\ApplicationContext;
use Hyperf\Stringable\Str;
use Throwable;

/**
 * Return the default value of the given value.
 */
function value(mixed $value, ...$args)
{
    return $value instanceof Closure ? $value(...$args) : $value;
}

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
            return null;
    }
    if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
        return substr($value, 1, -1);
    }
    return $value;
}

/**
 * Retry an operation a given number of times.
 *
 * @param float|int $times
 * @param int $sleep millisecond
 * @throws Throwable
 */
function retry($times, callable $callback, int $sleep = 0)
{
    $attempts = 0;
    $backoff = new Backoff($sleep);

    beginning:
    try {
        return $callback(++$attempts);
    } catch (Throwable $e) {
        if (--$times < 0) {
            throw $e;
        }

        $backoff->sleep();
        goto beginning;
    }
}

/**
 * Return the given value, optionally passed through the given callback.
 *
 * @param mixed $value
 */
function with($value, callable $callback = null)
{
    return is_null($callback) ? $value : $callback($value);
}

/**
 * Call a callback with the arguments.
 *
 * @param mixed $callback
 * @return null|mixed
 */
function call($callback, array $args = [])
{
    $result = null;
    if ($callback instanceof Closure) {
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

/**
 * Returns all traits used by a trait and its traits.
 *
 * @param object|string $trait
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

    /* @phpstan-ignore-next-line */
    foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
        $results += trait_uses_recursive($class);
    }

    return array_unique($results);
}

/**
 * Create a setter string.
 */
function setter(string $property): string
{
    return 'set' . Str::studly($property);
}

/**
 * Create a getter string.
 */
function getter(string $property): string
{
    return 'get' . Str::studly($property);
}

/**
 * Create an object instance, if the DI container exist in ApplicationContext,
 * then the object will be created by DI container via `make()` method, if not,
 * the object will create by `new` keyword.
 */
function make(string $name, array $parameters = [])
{
    if (ApplicationContext::hasContainer()) {
        /** @var \Hyperf\Di\Container $container */
        $container = ApplicationContext::getContainer();
        if (method_exists($container, 'make')) {
            return $container->make($name, $parameters);
        }
    }
    $parameters = array_values($parameters);
    return new $name(...$parameters);
}

/**
 * Return the default swoole hook flags, you can rewrite it by defining `SWOOLE_HOOK_FLAGS`.
 */
function swoole_hook_flags(): int
{
    return defined('SWOOLE_HOOK_FLAGS') ? SWOOLE_HOOK_FLAGS : SWOOLE_HOOK_ALL;
}

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
    return null;
}

/**
 * Build SQL contain bind.
 */
function build_sql(string $sql, array $bindings = []): string
{
    if (! Arr::isAssoc($bindings)) {
        $position = 0;
        foreach ($bindings as $value) {
            $position = strpos($sql, '?', $position);
            if ($position === false) {
                break;
            }

            $value = (string) match (gettype($value)) {
                'integer', 'double' => $value,
                'boolean' => (int) $value,
                default => sprintf("'%s'", $value),
            };
            $sql = substr_replace($sql, $value, $position, 1);
            $position += strlen($value);
        }
    }

    return $sql;
}
