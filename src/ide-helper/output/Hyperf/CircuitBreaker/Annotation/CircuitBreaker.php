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
namespace Hyperf\CircuitBreaker\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @property float $timeout
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractAnnotation
{
    public function __construct($handler, $fallback, $duration, $successCounter, $failCounter, $value)
    {
    }
}
