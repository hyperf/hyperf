<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Annotation;

use Hyperf\Di\MetadataCollector;

class AspectCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function setArround(string $aspect, array $classes, array $annotations)
    {
        // @TODO Remove this method, use collectClassAndAnnotation instead of.
        $arround = static::get('arround');
        $arround['classes'][$aspect] = array_replace($arround['classes'][$aspect] ?? [], $classes);
        $arround['annotations'][$aspect] = array_replace($arround['annotations'][$aspect] ?? [], $annotations);
        static::set('arround', $arround);

        static::collectClassAndAnnotation([$aspect], $classes, $annotations);
    }

    /**
     * Collect classes and annotations
     */
    public static function collectClassAndAnnotation(array $aspects, array $classes, array $annotations)
    {
        $staticClasses = static::get('class.static', []);
        $dynamicClasses = static::get('class.dynamic', []);
        $staticAnnotations = static::get('annotation.static', []);
        $dynamicAnnotations = static::get('annotation.dynamic', []);

        foreach ($classes as $class) {
            if (strpos($class, '*') === false) {
                $staticClasses[$class] = array_unique(array_merge($staticClasses[$class] ?? [], $aspects));
            } else {
                $dynamicClasses[$class] = array_unique(array_merge($dynamicClasses[$class] ?? [], $aspects));
            }
        }

        foreach ($annotations as $annotation) {
            if (strpos($annotation, '*') === false) {
                $staticAnnotations[$annotation] = array_unique(array_merge($staticAnnotations[$annotation] ?? [], $aspects));
            } else {
                $dynamicAnnotations[$annotation] = array_unique(array_merge($dynamicAnnotations[$annotation] ?? [], $aspects));
            }
        }

        static::set('class.static', $staticClasses);
        static::set('class.dynamic', $dynamicClasses);
        static::set('annotation.static', $staticAnnotations);
        static::set('annotation.dynamic', $dynamicAnnotations);
    }
}
