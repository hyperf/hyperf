<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Di\Stub;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class Foo2Aspect implements AroundInterface
{
    /**
     * The classes that you want to weaving.
     *
     * @var array
     */
    public $classes = [
        Foo::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
    }
}
