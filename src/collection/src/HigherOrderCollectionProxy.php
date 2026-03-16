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

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @mixin Enumerable
 * @mixin TValue
 */
class HigherOrderCollectionProxy
{
    /**
     * Create a new proxy instance.
     * @param Enumerable<TKey, TValue> $collection the collection being operated on
     * @param string $method the method being proxied
     */
    public function __construct(protected Enumerable $collection, protected string $method)
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
