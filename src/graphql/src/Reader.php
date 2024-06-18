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

namespace Hyperf\GraphQL;

use Hyperf\Di\Annotation\AnnotationReader as HyperfAnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Reader implements \Doctrine\Common\Annotations\Reader
{
    public function __construct(protected HyperfAnnotationReader $reader)
    {
    }

    public function getClassAnnotations(ReflectionClass $class)
    {
        return $this->reader->getClassAnnotations($class);
    }

    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        return $this->reader->getClassAnnotation($class, $annotationName);
    }

    public function getMethodAnnotations(ReflectionMethod $method)
    {
        return $this->reader->getMethodAnnotations($method);
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        return $this->reader->getMethodAnnotation($method, $annotationName);
    }

    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        return $this->reader->getPropertyAnnotations($property);
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        return $this->reader->getPropertyAnnotation($property, $annotationName);
    }
}
