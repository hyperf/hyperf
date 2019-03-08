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
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectClass(string $className, ?string $target): void
    {
        if ($this->value !== null) {
            AnnotationCollector::collectClass($className, static::class, $this);
        }
    }

    public function collectMethod(string $className, ?string $target): void
    {
        if ($this->value !== null) {
            AnnotationCollector::collectMethod($className, $target, static::class, $this);
        }
    }

    public function collectProperty(string $className, ?string $target): void
    {
        if ($this->value !== null) {
            AnnotationCollector::collectProperty($className, $target, static::class, $this);
        }
    }
}
