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

class DependenciesCollector extends MetadataCollector
{
    protected static array $container = [];

    protected static array $dependencies = [];

    public static function setDefinition(string $abstract, string $concrete): void
    {
        self::$dependencies[$abstract] = $concrete;
    }

    /**
     * Get the annotation dependencies.
     * @return array<string, class-string>
     */
    public static function getDefinitions(): array
    {
        return self::$dependencies;
    }
}
