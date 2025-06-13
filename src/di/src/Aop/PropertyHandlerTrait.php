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

namespace Hyperf\Di\Aop;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\ReflectionManager;
use ReflectionClass;

trait PropertyHandlerTrait
{
    protected function __handlePropertyHandler(string $className)
    {
        if (PropertyHandlerManager::isEmpty()) {
            return;
        }
        $reflectionClass = ReflectionManager::reflectClass($className);
        $properties = ReflectionManager::reflectPropertyNames($className);

        // Inject the properties of current class
        $handled = $this->__handle($className, $className, $properties);

        // Inject the properties of traits.
        // Because the property annotations of trait couldn't be collected by class.
        $handled = $this->__handleTrait($reflectionClass, $handled, $className);

        // Inject the properties of parent class.
        // It can be used to deal with parent classes whose subclasses have constructor function, but don't execute `parent::__construct()`.
        // For example:
        // class SubClass extend ParentClass
        // {
        //     public function __construct() {
        //     }
        // }
        $parentReflectionClass = $reflectionClass;
        while ($parentReflectionClass = $parentReflectionClass->getParentClass()) {
            $parentClassProperties = ReflectionManager::reflectPropertyNames($parentReflectionClass->getName());
            $parentClassProperties = array_filter($parentClassProperties, static function ($property) use ($reflectionClass) {
                return $reflectionClass->hasProperty($property);
            });
            $parentClassProperties = array_diff($parentClassProperties, $handled);
            $handled = array_merge(
                $handled,
                $this->__handle($className, $parentReflectionClass->getName(), $parentClassProperties)
            );
        }
    }

    protected function __handleTrait(ReflectionClass $reflectionClass, array $handled, string $className): array
    {
        foreach ($reflectionClass->getTraits() ?? [] as $reflectionTrait) {
            if (in_array($reflectionTrait->getName(), [ProxyTrait::class, PropertyHandlerTrait::class])) {
                continue;
            }
            $traitProperties = ReflectionManager::reflectPropertyNames($reflectionTrait->getName());
            $traitProperties = array_diff($traitProperties, $handled);
            if (! $traitProperties) {
                continue;
            }
            $handled = array_merge(
                $handled,
                $this->__handle($className, $reflectionTrait->getName(), $traitProperties)
            );
            $handled = $this->__handleTrait($reflectionTrait, $handled, $className);
        }
        return $handled;
    }

    protected function __handle(string $currentClassName, string $targetClassName, array $properties): array
    {
        $handled = [];
        foreach ($properties as $propertyName) {
            $propertyMetadata = AnnotationCollector::getClassPropertyAnnotation($targetClassName, $propertyName);
            if (! $propertyMetadata) {
                continue;
            }
            foreach ($propertyMetadata as $annotationName => $annotation) {
                if ($callbacks = PropertyHandlerManager::get($annotationName)) {
                    foreach ($callbacks as $callback) {
                        $callback($this, $currentClassName, $targetClassName, $propertyName, $annotation);
                    }
                    $handled[] = $propertyName;
                }
            }
        }

        return $handled;
    }
}
