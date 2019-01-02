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
    }

    /**
     * {@inheritDoc}
     */
    public function collectClass(string $className, ?string $target): void
    {
        if (null !== $this->value) {
            AnnotationCollector::collectClass($className, static::class, $this->value);
        }
    }

    public function collectMethod(string $className, ?string $target): void
    {
        if (null !== $this->value) {
            AnnotationCollector::collectMethod($className, $target, static::class, $this->value);
        }
    }

    public function collectProperty(string $className, ?string $target): void
    {
        if (null !== $this->value) {
            AnnotationCollector::collectProperty($className, $target, static::class, $this->value);
        }
    }

}
