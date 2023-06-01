<?php

declare(strict_types=1);

namespace Hyperf\Metric\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class Metric extends AbstractAnnotation
{
}
