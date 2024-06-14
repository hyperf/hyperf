<?php

declare(strict_types=1);

namespace HyperfTest\Di\Stub\Collect\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class MethodAnnotation extends AbstractAnnotation
{

}