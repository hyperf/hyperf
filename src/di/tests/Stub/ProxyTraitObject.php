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
namespace HyperfTest\Di\Stub;

use Hyperf\Di\Aop\ProxyTrait;

class ProxyTraitObject
{
    use ProxyTrait;

    public function get(?int $id, string $str = '')
    {
        return $this->__getParamsMap(static::class, 'get', func_get_args());
    }

    public function get2(?int $id = 1, string $str = '')
    {
        return $this->__getParamsMap(static::class, 'get2', func_get_args());
    }

    public function get3(?int $id = 1, string $str = '', float $num = 1.0)
    {
        return $this->__getParamsMap(static::class, 'get3', func_get_args());
    }

    public function incr()
    {
        $__function__ = __FUNCTION__;
        $__method__ = __METHOD__;
        return self::__proxyCall(ProxyTraitObject::class, __FUNCTION__, self::__getParamsMap(ProxyTraitObject::class, __FUNCTION__, func_get_args()), function () use ($__function__, $__method__) {
            return 1;
        });
    }
}
