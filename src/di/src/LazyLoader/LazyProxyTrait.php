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

namespace Hyperf\Di\LazyLoader;

use Hyperf\Context\ApplicationContext;

trait LazyProxyTrait
{
    public function __construct()
    {
        $vars = get_object_vars($this);
        foreach (array_keys($vars) as $var) {
            unset($this->{$var});
        }
    }

    public function __call($method, $arguments)
    {
        $obj = $this->getInstance();
        return call_user_func([$obj, $method], ...$arguments);
    }

    public function __get($name)
    {
        return $this->getInstance()->{$name};
    }

    public function __set($name, $value)
    {
        $this->getInstance()->{$name} = $value;
    }

    public function __isset($name)
    {
        return isset($this->getInstance()->{$name});
    }

    public function __unset($name)
    {
        unset($this->getInstance()->{$name});
    }

    public function __wakeup()
    {
        $vars = get_object_vars($this);
        foreach (array_keys($vars) as $var) {
            unset($this->{$var});
        }
    }

    /**
     * Return The Proxy Target.
     * @return mixed
     */
    public function getInstance()
    {
        return ApplicationContext::getContainer()->get(self::PROXY_TARGET);
    }
}
