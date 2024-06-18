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
        return [];
    }

    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        return null;
    }

    public function getMethodAnnotations(ReflectionMethod $method)
    {
        return [];
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        return null;
    }

    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        return [];
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        return null;
    }
}
