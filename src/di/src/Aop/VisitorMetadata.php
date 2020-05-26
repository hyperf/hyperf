<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Aop;

/**
 * @property string className
 * @property bool hasConstructor
 */
class VisitorMetadata
{
    public function __get($name)
    {
        return $this->{$name} ?? null;
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    public function __isset($name)
    {
        return isset($this->{$name});
    }

    public function __unset($name)
    {
        unset($this->{$name});
    }
}
