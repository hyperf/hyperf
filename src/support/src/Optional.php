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

use ArrayAccess;
use Hyperf\Collection\Arr;
use Hyperf\Macroable\Macroable;

class Optional implements ArrayAccess
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Create a new optional instance.
     *
     * @param mixed $value the underlying object
     */
    public function __construct(protected $value)
    {
    }

    /**
     * Dynamically access a property on the underlying object.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (is_object($this->value)) {
            return $this->value->{$key} ?? null;
        }

        return null;
    }

    /**
     * Dynamically check a property exists on the underlying object.
     *
     * @param mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        if (is_object($this->value)) {
            return isset($this->value->{$name});
        }

        return false;
    }

    /**
     * Dynamically pass a method to the underlying object.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (is_object($this->value)) {
            return $this->value->{$method}(...$parameters);
        }

        return null;
    }

    /**
     * Determine if an item exists at an offset.
     */
    public function offsetExists(mixed $offset): bool
    {
        return Arr::accessible($this->value) && Arr::exists($this->value, $offset);
    }

    /**
     * Get an item at a given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return Arr::get($this->value, $offset);
    }

    /**
     * Set the item at a given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (Arr::accessible($this->value)) {
            $this->value[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        if (Arr::accessible($this->value)) {
            unset($this->value[$offset]);
        }
    }
}
