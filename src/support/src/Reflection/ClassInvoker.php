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
namespace Hyperf\Support\Reflection;

use ReflectionClass;

class ClassInvoker
{
    protected ReflectionClass $reflection;

    public function __construct(protected object $instance)
    {
        $this->reflection = new ReflectionClass($instance);
    }

    public function __get($name)
    {
        $property = $this->reflection->getProperty($name);

        $property->setAccessible(true);

        return $property->getValue($this->instance);
    }

    public function __call($name, $arguments)
    {
        $method = $this->reflection->getMethod($name);

        $method->setAccessible(true);

        return $method->invokeArgs($this->instance, $arguments);
    }
}
