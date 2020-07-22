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

use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\CircuitBreaker\Handler\TimeoutHandler;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @property $timeout
 */
class CircuitBreaker extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $handler = TimeoutHandler::class;

    /**
     * @var string
     */
    public $fallback;

    /**
     * The duration required to reset to a half open or close state.
     * @var float
     */
    public $duration = 10;

    /**
     * The counter required to reset to a close state.
     * @var int
     */
    public $successCounter = 10;

    /**
     * The counter required to reset to a open state.
     * @var int
     */
    public $failCounter = 10;

    /**
     * @var array
     */
    public $value;

    public function __construct($value = null)
    {
        parent::__construct($value);

        $this->value = $value ?? [];
    }
}
