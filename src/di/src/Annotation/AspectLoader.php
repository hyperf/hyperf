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

use Hyperf\Di\ReflectionManager;
use ReflectionProperty;

class AspectLoader
{
    /**
     * Load classes annotations and priority from aspect without invoking their constructor.
     */
    public static function load(string $className): array
    {
        $reflectionClass = ReflectionManager::reflectClass($className);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $instanceClasses = $instanceAnnotations = [];
        $instancePriority = null;
        foreach ($properties as $property) {
            if ($property->getName() === 'classes') {
                $instanceClasses = ReflectionManager::getPropertyDefaultValue($property);
            } elseif ($property->getName() === 'annotations') {
                $instanceAnnotations = ReflectionManager::getPropertyDefaultValue($property);
            } elseif ($property->getName() === 'priority') {
                $instancePriority = ReflectionManager::getPropertyDefaultValue($property);
            }
        }

        return [$instanceClasses, $instanceAnnotations, $instancePriority];
    }
}
