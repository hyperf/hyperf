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

    public static function getClassAnnotation(string $class, string $annotation)
    {
        return static::get($class . '._c.' . $annotation);
    }

    public static function getClassMethodAnnotation(string $class, string $method)
    {
        return static::get($class . '._m.' . $method);
    }

    public static function getMethodByAnnotation(string $annotation): array
    {
        $result = [];
        foreach (static::$container as $class => $metadata) {
            foreach ($metadata['_m'] ?? [] as $method => $_metadata) {
                if ($value = $_metadata[$annotation] ?? null) {
                    $result[] = ['class' => $class, 'method' => $method, 'annotation' => $value];
                }
            }
        }
        return $result;
    }
}
