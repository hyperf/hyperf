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

/**
 * This collector is used to collect the relation of parent class and sub-class, also include the trait and sub-class.
 */
class RelationCollector
{
    public static array $container = [];

    public static function addRelation(string $key, string $className): void
    {
        static::$container[$key][] = $className;
    }

    public static function getRelation(string $key): array
    {
        return static::getContainer()[$key] ?? [];
    }

    public static function getContainer(): array
    {
        return static::$container;
    }
}
