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

namespace HyperfTest\Di\Stub\Collect;

use HyperfTest\Di\Stub\Collect\Annotation\ClassAnnotation;
use HyperfTest\Di\Stub\Collect\Annotation\ClassConstantAnnotation;
use HyperfTest\Di\Stub\Collect\Annotation\MethodAnnotation;
use HyperfTest\Di\Stub\Collect\Annotation\PropertyAnnotation;

#[ClassAnnotation]
class Foo
{
    #[ClassConstantAnnotation]
    public const FOO = 'foo';

    #[PropertyAnnotation]
    protected string $foo;

    #[MethodAnnotation]
    public function method()
    {
    }
}
