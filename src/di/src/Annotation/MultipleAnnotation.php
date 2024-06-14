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

namespace Hyperf\Di\Annotation;

use Hyperf\Di\Exception\AnnotationException;

class MultipleAnnotation implements MultipleAnnotationInterface
{
    protected array $annotations = [];

    protected string $className;

    public function __construct(AnnotationInterface $annotation)
    {
        $this->annotations = [$annotation];
        $this->className = get_class($annotation);
    }

    public function __get(string $name)
    {
        if (count($this->annotations) > 1) {
            throw new AnnotationException('MultipleAnnotation[' . $this->className() . '] has more than one annotations.');
        }

        return $this->annotations[0]->{$name};
    }

    public function className(): string
    {
        return $this->className;
    }

    public function insert(AnnotationInterface $annotation): static
    {
        if (! $annotation instanceof $this->className) {
            throw new AnnotationException(get_class($annotation) . ' must instanceof ' . $this->className);
        }

        $this->annotations[] = $annotation;
        return $this;
    }

    public function toAnnotations(): array
    {
        return $this->annotations;
    }

    public function collectClass(string $className): void
    {
        throw new AnnotationException('MultipleAnnotation[' . $this->className() . '] does not support collectClass()');
    }

    public function collectClassConstant(string $className, ?string $target): void
    {
        throw new AnnotationException('MultipleAnnotation[' . $this->className() . '] does not support collectClassConstant()');
    }

    public function collectMethod(string $className, ?string $target): void
    {
        throw new AnnotationException('MultipleAnnotation[' . $this->className() . '] does not support collectMethod()');
    }

    public function collectProperty(string $className, ?string $target): void
    {
        throw new AnnotationException('MultipleAnnotation[' . $this->className() . '] does not support collectProperty()');
    }
}
