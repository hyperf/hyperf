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
        $traitNames = $reflectionClass->getTraitNames();
        if (is_array($traitNames)) {
            foreach ($traitNames ?? [] as $traitName) {
                $traitProperties = ReflectionManager::reflectPropertyNames($traitName);
                $handled = $this->__diffHandle($className, $traitName, $traitProperties, $handled);
            }
        }

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
            $handled = $this->__diffHandle($className, $parentReflectionClass->getName(), $parentClassProperties, $handled);
        }
    }

    private function __diffHandle(string $currentClassName, string $targetClassName, array $properties, array $excludeProperties): array
    {
        $properties = array_diff($properties, $excludeProperties);
        return array_merge($excludeProperties, $this->__handle($currentClassName, $targetClassName, $properties));
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
                        call($callback, [$this, $currentClassName, $targetClassName, $propertyName, $annotation]);
                    }
                    $handled[] = $propertyName;
                }
            }
        }

        return $handled;
    }
}
