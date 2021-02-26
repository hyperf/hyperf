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

use Hyperf\Di\BetterReflectionManager;
use ReflectionProperty;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Aspect extends AbstractAnnotation
{
    /**
     * @var array
     */
    public $classes = [];

    /**
     * @var array
     */
    public $annotations = [];

    /**
     * @var null|int
     */
    public $priority;

    public function collectClass(string $className): void
    {
        parent::collectClass($className);
        $this->collect($className);
    }

    protected function collect(string $className)
    {
        // Create the aspect instance without invoking their constructor.
        $reflectionClass = BetterReflectionManager::reflectClass($className);
        $properties = $reflectionClass->getImmediateProperties(ReflectionProperty::IS_PUBLIC);
        $instanceClasses = $instanceAnnotations = [];
        $instancePriority = null;
        foreach ($properties as $property) {
            if ($property->getName() === 'classes') {
                $instanceClasses = $property->getDefaultValue();
            } elseif ($property->getName() === 'annotations') {
                $instanceAnnotations = $property->getDefaultValue();
            } elseif ($property->getName() === 'priority') {
                $instancePriority = $property->getDefaultValue();
            }
        }

        // Classes
        $classes = $this->classes;
        $classes = $instanceClasses ? array_merge($classes, $instanceClasses) : $classes;
        // Annotations
        $annotations = $this->annotations;
        $annotations = $instanceAnnotations ? array_merge($annotations, $instanceAnnotations) : $annotations;
        // Priority
        $annotationPriority = $this->priority;
        $propertyPriority = $instancePriority ? $instancePriority : null;
        if (! is_null($annotationPriority) && ! is_null($propertyPriority) && $annotationPriority !== $propertyPriority) {
            throw new \InvalidArgumentException('Cannot define two difference priority of Aspect.');
        }
        $priority = $annotationPriority ?? $propertyPriority;
        // Save the metadata to AspectCollector
        AspectCollector::setAround($className, $classes, $annotations, $priority);
    }
}
