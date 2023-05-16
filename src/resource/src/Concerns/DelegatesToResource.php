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
namespace Hyperf\Resource\Concerns;

use Exception;
use Hyperf\Resource\Exception\ResourceException;
use Hyperf\Support\Traits\ForwardsCalls;

trait DelegatesToResource
{
    use ForwardsCalls;

    /**
     * Determine if an attribute exists on the resource.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->resource->{$key});
    }

    /**
     * Unset an attribute on the resource.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->resource->{$key});
    }

    /**
     * Dynamically get properties from the underlying resource.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->resource->{$key};
    }

    /**
     * Dynamically pass method calls to the underlying resource.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->resource, $method, $parameters);
    }

    /**
     * Get the value of the resource's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->resource->getRouteKey();
    }

    /**
     * Get the route key for the resource.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->resource->getRouteKeyName();
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     *
     * @throws Exception
     */
    public function resolveRouteBinding($value)
    {
        throw new ResourceException('Resources may not be implicitly resolved from route bindings.');
    }

    /**
     * Determine if the given attribute exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->resource);
    }

    /**
     * Get the value for a given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->resource[$offset];
    }

    /**
     * Set the value for a given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->resource[$offset] = $value;
    }

    /**
     * Unset the value for a given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->resource[$offset]);
    }
}
