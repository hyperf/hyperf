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

    public function __construct(public string $name = 'Hyperf')
    {
    }

    public function get(?int $id, string $str = '')
    {
        return ['order' => ['id', 'str'], 'keys' => compact('id', 'str'), 'variadic' => ''];
    }

    public function get2(?int $id = 1, string $str = '')
    {
        return ['order' => ['id', 'str'], 'keys' => compact('id', 'str'), 'variadic' => ''];
    }

    public function get3(?int $id = 1, string $str = '', float $num = 1.0)
    {
        return ['order' => ['id', 'str', 'num'], 'keys' => compact('id', 'str', 'num'), 'variadic' => ''];
    }

    public function get4(?int $id = 1, string ...$variadic)
    {
        return ['order' => ['id', 'variadic'], 'keys' => compact('id', 'variadic'), 'variadic' => 'variadic'];
    }

    public function incr()
    {
        return self::__proxyCall(__TRAIT__, __FUNCTION__, ['keys' => []], function () {
            return 1;
        });
    }

    public function getName()
    {
        return self::__proxyCall(__TRAIT__, __FUNCTION__, ['keys' => []], function () {
            return 'HyperfCloud';
        });
    }

    public function getName2()
    {
        return self::__proxyCall(__TRAIT__, __FUNCTION__, ['keys' => []], function () {
            return 'HyperfCloud';
        });
    }
}
