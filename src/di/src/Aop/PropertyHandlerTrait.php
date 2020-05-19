<?php

namespace Hyperf\Di\Aop;


use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\Definition\PropertyHandlerManager;

trait PropertyHandlerTrait
{

    protected function __handlePropertyHandler(string $className)
    {
        $propertyHandlers = PropertyHandlerManager::all();
        if (! $propertyHandlers) {
            return;
        }
        // Inject the properties of current class
        $reflectionProperties = BetterReflectionManager::reflectClass($className)->getProperties();
        $this->__handle($className, $className, $propertyHandlers, $reflectionProperties);
        // Inject the properties of parent class
        $reflectionClass = BetterReflectionManager::reflectClass($className);
        $parentClassNames = $reflectionClass->getParentClassNames();
        foreach ($parentClassNames ?? [] as $parentClassName) {
            $reflectionProperties = BetterReflectionManager::reflectClass($parentClassName)->getProperties();
            $this->__handle($className, $parentClassName, $propertyHandlers, $reflectionProperties);
        }
        // Inject the properties of traits
        $traitNames = $reflectionClass->getTraitNames();
        foreach ($traitNames ?? [] as $traitName) {
            $reflectionProperties = BetterReflectionManager::reflectClass($traitName)->getProperties();
            $this->__handle($className, $traitName, $propertyHandlers, $reflectionProperties);
        }
    }

    protected function __handle(string $currentClassName, string $targetClassName, array $propertyHandlers, array $reflectionProperties)
    {
        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyMetadata = AnnotationCollector::getClassPropertyAnnotation($targetClassName, $reflectionProperty->getName());
            if (! $propertyMetadata) {
                continue;
            }
            foreach ($propertyMetadata as $annotationName => $annotation) {
                if (isset($propertyHandlers[$annotationName])) {
                    $callbacks = $propertyHandlers[$annotationName];
                    foreach ($callbacks as $callback) {
                        call($callback, [$this, $currentClassName, $targetClassName, $reflectionProperty->getName(), $annotation]);
                    }
                }
            }
        }
    }

}