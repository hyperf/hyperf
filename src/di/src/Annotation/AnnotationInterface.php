<?php

namespace Hyperf\Di\Annotation;


interface AnnotationInterface
{

    /**
     * @return string Collect the annotation metadata to a container that you wants.
     */
    public function collect(string $className, ?string $target): void;

}