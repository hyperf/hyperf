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
namespace Hyperf\Swagger\Annotation;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;

trait MultipleAnnotationTrait
{
    public function collectClass(string $className): void
    {
        $annotation = AnnotationCollector::getClassAnnotation($className, static::class);

        AnnotationCollector::collectClass($className, static::class, $this->formatAnnotation($annotation));
    }

    public function collectMethod(string $className, ?string $target): void
    {
        $annotation = AnnotationCollector::getClassMethodAnnotation($className, $target)[static::class] ?? null;

        AnnotationCollector::collectMethod($className, $target, static::class, $this->formatAnnotation($annotation));
    }

    public function collectProperty(string $className, ?string $target): void
    {
        $annotation = AnnotationCollector::getClassPropertyAnnotation($className, $target)[static::class] ?? null;

        AnnotationCollector::collectProperty($className, $target, static::class, $this->formatAnnotation($annotation));
    }

    protected function formatAnnotation(?MultipleAnnotation $annotation): MultipleAnnotation
    {
        if ($annotation instanceof MultipleAnnotation) {
            return $annotation->insert($this);
        }

        return new MultipleAnnotation($this);
    }
}
