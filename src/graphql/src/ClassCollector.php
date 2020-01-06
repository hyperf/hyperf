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

namespace Hyperf\GraphQL;

class ClassCollector
{
    private static $classes = [];

    public static function collect(string $class)
    {
        if (! in_array($class, self::$classes)) {
            self::$classes[] = $class;
        }
    }

    public static function getClasses()
    {
        return self::$classes;
    }
}
