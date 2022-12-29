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
namespace Hyperf\GraphQL;

use Hyperf\Di\MetadataCollector;

class ClassCollector extends MetadataCollector
{
    protected static array $container = [];

    public static function collect(string $class)
    {
        if (! in_array($class, self::$container)) {
            self::$container[] = $class;
        }
    }

    public static function getClasses()
    {
        return self::$container;
    }
}
