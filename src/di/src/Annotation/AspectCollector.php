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

class AspectCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @var array
     */
    protected static $aspectRules = [];

    /**
     * @var \SplPriorityQueue[]
     */
    protected static $aspectQueues = [];

    public static function setAround(string $aspect, array $classes, array $annotations, ?int $priority = null): void
    {
        if (! is_int($priority)) {
            $priority = static::getDefaultPriority();
        }
        static::set('classes.' . $aspect, $classes);
        static::set('annotations.' . $aspect, $annotations);
        static::$aspectRules[$aspect] = [
            'priority' => $priority,
            'classes' => $classes,
            'annotations' => $annotations,
        ];
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
        return (int) (PHP_INT_MAX / 2);
    }

}
