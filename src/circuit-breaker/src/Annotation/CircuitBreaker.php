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
use Hyperf\CircuitBreaker\Handler\TimeoutHandler;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @property float $timeout
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CircuitBreaker extends AbstractAnnotation
{
    public string $handler = TimeoutHandler::class;

    public ?string $fallback = null;

    /**
     * The duration required to reset to a half open or close state.
     */
    public float $duration = 10;

    /**
     * The counter required to reset to a close state.
     */
    public int $successCounter = 10;

    /**
     * The counter required to reset to an open state.
     */
    public int $failCounter = 10;

    public array $value;

    public function __construct(...$value)
    {
        parent::__construct(...$value);

        $this->value = $this->formatParams($value);
    }
}
