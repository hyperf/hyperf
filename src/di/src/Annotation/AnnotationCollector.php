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

class AnnotationCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class]['_c'][$annotation] = $value;
    }

    public static function collectProperty(string $class, string $property, string $annotation, $value): void
    {
        static::$container[$class]['_p'][$property][$annotation] = $value;
    }

    public static function collectMethod(string $class, string $method, string $annotation, $value): void
    {
        static::$container[$class]['_m'][$method][$annotation] = $value;
    }

    public static function getClassByAnnotation(string $annotation): array
    {
        $result = [];
        foreach (static::$container as $class => $metadata) {
            if (! isset($metadata['_c'][$annotation])) {
                continue;
            }
            $result[$class] = $metadata['_c'][$annotation];
        }
        return $result;
    }

}
