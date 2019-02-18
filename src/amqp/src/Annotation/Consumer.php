<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Consumer extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $exchange;

    /**
     * @var string
     */
    public $routingKey;

    /**
     * @var string
     */
    public $queue;

    /**
     * @var int
     */
    public $nums = 0;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['exchange'])) {
            $this->exchange = $value['exchange'];
        }
        if (isset($value['routingKey'])) {
            $this->routingKey = $value['routingKey'];
        }
        if (isset($value['queue'])) {
            $this->queue = $value['queue'];
        }
        if (isset($value['nums']) && is_numeric($value['nums'])) {
            $this->nums = (int) $value['nums'];
        }
    }

    public function collectClass(string $className, ?string $target): void
    {
        if ($this->value !== null) {
            AnnotationCollector::collectClass($className, static::class, $this);
        }
    }
}
