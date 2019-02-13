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
use Doctrine\Instantiator\Instantiator;

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

    public static function setArround(string $aspect, array $classes, array $annotations): void
    {
        $savedClasses = static::get('classes.' . $aspect, []);
        $savedAnnotations = static::get('annotations.' . $aspect, []);
        $classes && static::set('classes.' . $aspect, array_replace($savedClasses, $classes));
        $annotations && static::set('annotations.' . $aspect, array_replace($savedAnnotations, $annotations));
        static::collectRules($aspect);
    }

    public static function getRule(string $aspect): array
    {
        return static::$aspectRules[$aspect] ?? [];
    }

    public static function getRules(): array
    {
        return static::$aspectRules;
    }

    private static function collectRules(string $aspect)
    {
        $instantiator = new Instantiator();
        $instance = $instantiator->instantiate($aspect);
        static::$aspectRules[$aspect] = [
            'classes' => $instance->classes ?? '',
            'annotations' => $instance->annotations ?? '',
        ];
    }
}
