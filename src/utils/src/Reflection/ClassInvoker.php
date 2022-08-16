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
namespace Hyperf\Utils\Reflection;

class ClassInvoker
{
    public function __construct(protected object $instance)
    {
    }

    public function __get($name)
    {
        return (fn() => $this->$name)->call($this->instance);
    }

    public function __set($name, $value)
    {
        return (fn() => $this->$name = $value)->call($this->instance);
    }

    public function __call($name, $arguments)
    {
        return (fn() => $this->$name(...$arguments))->call($this->instance);
    }
}
