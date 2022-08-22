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

trait ProxyTraitOnTrait
{
    use ProxyTrait;

    /**
     * @var string
     */
    public $name;

    public function __construct(string $name = 'Hyperf')
    {
        $this->name = $name;
    }

    public function get(?int $id, string $str = '')
    {
        return $this->__getParamsMap(__TRAIT__, 'get', func_get_args());
    }

    public function get2(?int $id = 1, string $str = '')
    {
        return $this->__getParamsMap(__TRAIT__, 'get2', func_get_args());
    }

    public function get3(?int $id = 1, string $str = '', float $num = 1.0)
    {
        return $this->__getParamsMap(__TRAIT__, 'get3', func_get_args());
    }

    public function incr()
    {
        return self::__proxyCall(__TRAIT__, __FUNCTION__, self::__getParamsMap(__TRAIT__, __FUNCTION__, func_get_args()), function () {
            return 1;
        });
    }

    public function getName()
    {
        return self::__proxyCall(__TRAIT__, __FUNCTION__, self::__getParamsMap(__TRAIT__, __FUNCTION__, func_get_args()), function () {
            return 'HyperfCloud';
        });
    }

    public function getName2()
    {
        return self::__proxyCall(__TRAIT__, __FUNCTION__, self::__getParamsMap(__TRAIT__, __FUNCTION__, func_get_args()), function () {
            return 'HyperfCloud';
        });
    }
}
