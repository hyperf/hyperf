<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Annotation;

use Doctrine\Instantiator\Instantiator;
use Hyperf\Di\Aop\AroundInterface;

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
     * @var int
     */
    public $priority;

    public function collectClass(string $className): void
    {
        $this->collect($className);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        $this->collect($className);
    }

    protected function collect(string $className)
    {
        if (class_exists($className)) {
            // Create the aspect instance without invoking their constructor.
            $instantitor = new Instantiator();
            $instance = $instantitor->instantiate($className);
            switch ($instance) {
                case $instance instanceof AroundInterface:
                    // Classes
                    $classes = $this->classes;
                    $classes = property_exists($instance, 'classes') ? array_merge($classes, $instance->classes) : $classes;
                    // Annotations
                    $annotations = $this->annotations;
                    $annotations = property_exists($instance, 'annotations') ? array_merge($annotations, $instance->annotations) : $annotations;
                    // Priority
                    $annotationPriority = $this->priority;
                    $propertyPriority = property_exists($instance, 'priority') ? $instance->priority : null;
                    if (! is_null($annotationPriority) && ! is_null($propertyPriority) && $annotationPriority !== $propertyPriority) {
                        throw new \InvalidArgumentException('Cannot define two difference priority of Aspect.');
                    }
                    $priority = $annotationPriority ?? $propertyPriority;
                    // Save the metadata to AspectCollector
                    AspectCollector::setAround($className, $classes, $annotations, $priority);
                    break;
            }
        }
    }
}
