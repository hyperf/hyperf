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
use Closure;
use Hyperf\CircuitBreaker\Handler\TimeoutHandler;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractAnnotation
{
    /**
     * @param float $duration the duration required to reset to a half open or close state
     * @param int $successCounter the counter required to reset to a close state
     * @param int $failCounter the counter required to reset to an open state
     * @param array $options ['timeout' => 1]
     */
    public function __construct(
        public string $handler = TimeoutHandler::class,
        public array|Closure|string $fallback = [],
        public float $duration = 10.0,
        public int $successCounter = 10,
        public int $failCounter = 10,
        public array $options = []
    ) {
    }
}
