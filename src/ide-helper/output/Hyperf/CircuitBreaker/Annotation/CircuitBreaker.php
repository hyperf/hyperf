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
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @property float $timeout
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractAnnotation
{
    public function __construct(string $handler = 'Hyperf\CircuitBreaker\Handler\TimeoutHandler', ?string $fallback = null, float $duration = 10.0, int $successCounter = 10, int $failCounter = 10, ?array $value = null)
    {
    }
}
