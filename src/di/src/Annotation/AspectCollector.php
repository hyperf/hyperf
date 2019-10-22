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

    public static function setAround(string $aspect, array $classes, array $annotations): void
    {
        static::set('classes.' . $aspect, $classes);
        static::set('annotations.' . $aspect, $annotations);
        static::$aspectRules[$aspect] = [
            'classes' => $classes,
            'annotations' => $annotations,
        ];
    }

    public static function getRule(string $aspect): array
    {
        return static::$aspectRules[$aspect] ?? [];
    }

    public static function getRules(): array
    {
        return static::$aspectRules;
    }
}
