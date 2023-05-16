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
namespace Hyperf\Utils;

/**
 * @mixin Collection
 * Most of the methods in this file come from illuminate/collections,
 * thanks Laravel Team provide such a useful class.
 *
 * @deprecated since 3.1, please use \Hyperf\Collection\HigherOrderCollectionProxy instead.
 */
class HigherOrderCollectionProxy
{
    /**
     * Create a new proxy instance.
     * @param Collection $collection the collection being operated on
     * @param string $method the method being proxied
     */
    public function __construct(protected Collection $collection, protected string $method)
    {
    }

    /**
     * Proxy accessing an attribute onto the collection items.
     */
    public function __get(string $key)
    {
        return $this->collection->{$this->method}(function ($value) use ($key) {
            return is_array($value) ? $value[$key] : $value->{$key};
        });
    }

    /**
     * Proxy a method call onto the collection items.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}
