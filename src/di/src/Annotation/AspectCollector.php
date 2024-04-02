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

use Hyperf\Di\MetadataCollector;

class AspectCollector extends MetadataCollector
{
    protected static array $container = [];

    protected static array $aspectRules = [];

    public static function setAround(string $aspect, array $classes, array $annotations, ?int $priority = null): void
    {
        if (! is_int($priority)) {
            $priority = static::getDefaultPriority();
        }
        $setter = function ($key, $value) {
            if (static::has($key)) {
                $value = array_merge(static::get($key, []), $value);
            }
            static::set($key, $value);
        };
        $setter('classes.' . $aspect, $classes);
        $setter('annotations.' . $aspect, $annotations);
        if (isset(static::$aspectRules[$aspect])) {
            static::$aspectRules[$aspect] = [
                'priority' => $priority,
                'classes' => array_merge(static::$aspectRules[$aspect]['classes'] ?? [], $classes),
                'annotations' => array_merge(static::$aspectRules[$aspect]['annotations'] ?? [], $annotations),
            ];
        } else {
            static::$aspectRules[$aspect] = [
                'priority' => $priority,
                'classes' => $classes,
                'annotations' => $annotations,
            ];
        }
    }

    public static function clear(?string $key = null): void
    {
        if ($key) {
            unset(static::$container['classes'][$key], static::$container['annotations'][$key], static::$aspectRules[$key]);
        } else {
            static::$container = [];
            static::$aspectRules = [];
        }
    }

    public static function getRule(string $aspect): array
    {
        return static::$aspectRules[$aspect] ?? [];
    }

    public static function getPriority(string $aspect): int
    {
        return static::$aspectRules[$aspect]['priority'] ?? static::getDefaultPriority();
    }

    public static function getRules(): array
    {
        return static::$aspectRules;
    }

    public static function getContainer(): array
    {
        return static::$container;
    }

    public static function serialize(): string
    {
        return serialize([static::$aspectRules, static::$container]);
    }

    public static function deserialize(string $metadata): bool
    {
        [$rules, $container] = unserialize($metadata);
        static::$aspectRules = $rules;
        static::$container = $container;
        return true;
    }

    private static function getDefaultPriority(): int
    {
        return 0;
    }
}
