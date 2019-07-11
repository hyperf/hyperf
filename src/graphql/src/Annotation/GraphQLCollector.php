<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GraphQL\Annotation;

use Hyperf\Di\MetadataCollector;

class GraphQLCollector extends MetadataCollector
{
    public static function collectMethod(string $annotation, string $class, string $method)
    {
        static::$container[$annotation][] = compact('class', 'method');
    }

    public static function collectClass(string $annotation, string $class)
    {
        static::$container[$annotation][] = $class;
    }

    public static function getClassByAnnotation(string $annotation)
    {
        $data = static::$container[$annotation] ?? [];
        return array_column($data, 'class');
    }

    public static function getClass(string $annotation)
    {
        return static::$container[$annotation] ?? [];
    }
}
