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

use Hyperf\Di\Exception\NotFoundException;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

/**
 * A reader for docblock annotations.
 */
class AnnotationReader
{
    public function __construct(protected array $ignoreAnnotations = [])
    {
    }

    public function getClassAnnotations(ReflectionClass $class)
    {
        return $this->getAttributes($class);
    }

    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        $annotations = $this->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        return $this->getAttributes($property);
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        $annotations = $this->getPropertyAnnotations($property);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    public function getMethodAnnotations(ReflectionMethod $method)
    {
        return $this->getAttributes($method);
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        $annotations = $this->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    public function getAttributes(Reflector $reflection): array
    {
        $result = [];
        if (! method_exists($reflection, 'getAttributes')) {
            return $result;
        }
        $attributes = $reflection->getAttributes();
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), $this->ignoreAnnotations, true)) {
                continue;
            }
            if (! class_exists($attribute->getName())) {
                $className = $methodName = $propertyName = $classConstantName = '';
                if ($reflection instanceof ReflectionClass) {
                    $className = $reflection->getName();
                } elseif ($reflection instanceof ReflectionMethod) {
                    $className = $reflection->getDeclaringClass()->getName();
                    $methodName = $reflection->getName();
                } elseif ($reflection instanceof ReflectionProperty) {
                    $className = $reflection->getDeclaringClass()->getName();
                    $propertyName = $reflection->getName();
                } elseif ($reflection instanceof ReflectionClassConstant) {
                    $className = $reflection->getDeclaringClass()->getName();
                    $classConstantName = $reflection->getName();
                }
                $message = sprintf(
                    "No attribute class found for '%s' in %s",
                    $attribute->getName(),
                    $className
                );
                if ($methodName) {
                    $message .= sprintf('->%s() method', $methodName);
                }
                if ($propertyName) {
                    $message .= sprintf('::$%s property', $propertyName);
                }
                if ($classConstantName) {
                    $message .= sprintf('::%s class constant', $classConstantName);
                }
                throw new NotFoundException($message);
            }
            $result[] = $attribute->newInstance();
        }
        return $result;
    }
}
