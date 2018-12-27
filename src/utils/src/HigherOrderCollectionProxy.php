<?php

namespace Hyperf\Utils;

/**
 * @mixin Collection
 * Most of the methods in this file come from illuminate/support,
 * thanks Laravel Team provide such a useful class.
 */
class HigherOrderCollectionProxy
{

    /**
     * The collection being operated on.
     *
     * @var Collection
     */
    protected $collection;

    /**
     * The method being proxied.
     *
     * @var string
     */
    protected $method;

    /**
     * Create a new proxy instance.
     */
    public function __construct(Collection $collection, string $method)
    {
        $this->method = $method;
        $this->collection = $collection;
    }

    /**
     * Proxy accessing an attribute onto the collection items.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->collection->{$this->method}(function ($value) use ($key) {
            return is_array($value) ? $value[$key] : $value->{$key};
        });
    }

    /**
     * Proxy a method call onto the collection items.
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }

}