<?php

namespace Hyperf\Di\Annotation;


abstract class AbstractAnnotation implements AnnotationInterface
{

    /**
     * @var array
     */
    public $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function collect(string $className, ?string $target): void
    {
        if (isset($this->value)) {
            AnnotationCollector::collectClass($className, static::class, $this->value);
        }
    }

}