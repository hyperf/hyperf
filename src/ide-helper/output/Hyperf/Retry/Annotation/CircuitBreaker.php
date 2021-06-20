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
namespace Hyperf\Retry\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractRetry
{
    public function __construct($policies, $sleepStrategyClass, $maxAttempts, $circuitBreakerState, $base, $retryOnThrowablePredicate, $retryOnResultPredicate, $retryThrowables, $ignoreThrowables, $fallback)
    {
    }
}
