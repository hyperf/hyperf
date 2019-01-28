<?php

namespace Hyperf\Amqp\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Producer extends AbstractAnnotation
{

    /**
     * @var string
     */
    public $exchange;

    /**
     * @var string
     */
    public $routingKey;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['exchange'])) {
            $this->exchange = $value['exchange'];
        }
        if (isset($value['routingKey'])) {
            $this->routingKey = $value['routingKey'];
        }
    }

    public function collectClass(string $className, ?string $target): void
    {
        if ($this->value !== null) {
            AnnotationCollector::collectClass($className, static::class, [
                'exchange' => $this->exchange,
                'routingKey' => $this->routingKey,
            ]);
        }
    }

}