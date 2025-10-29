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

namespace Hyperf\ViewEngine\Component;

use ArrayIterator;
use Closure;
use Hyperf\Collection\Enumerable;
use Hyperf\ViewEngine\Contract\DeferringDisplayableValue;
use Hyperf\ViewEngine\Contract\Htmlable;
use IteratorAggregate;
use Stringable;
use Traversable;

class InvokableComponentVariable implements DeferringDisplayableValue, IteratorAggregate, Stringable
{
    /**
     * Create a new variable instance.
     *
     * @param Closure $callable the callable instance to resolve the variable value
     */
    public function __construct(protected Closure $callable)
    {
    }

    /**
     * Dynamically proxy attribute access to the variable.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->__invoke()->{$key};
    }

    /**
     * Dynamically proxy method access to the variable.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->__invoke()->{$method}(...$parameters);
    }

    /**
     * Resolve the variable.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func($this->callable);
    }

    /**
     * Resolve the variable as a string.
     */
    public function __toString(): string
    {
        return (string) $this->__invoke();
    }

    /**
     * Resolve the displayable value that the class is deferring.
     */
    public function resolveDisplayableValue(): Htmlable|string
    {
        return $this->__invoke();
    }

    /**
     * Get an iterator instance for the variable.
     */
    public function getIterator(): Traversable
    {
        $result = $this->__invoke();

        return new ArrayIterator($result instanceof Enumerable ? $result->all() : $result);
    }
}
