<?php

declare(strict_types=1);

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