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

use Hyperf\Utils\Filesystem\Filesystem;

class ClassCollector
{
    private static $classes = [];

    public static function collect(string $class)
    {
        if (! in_array($class, self::$classes)) {
            self::$classes[] = $class;
        }
        $filesystem = new Filesystem();
        $filesystem->put(BASE_PATH . '/runtime/container/graphql.cache', serialize(self::$classes));
    }

    public static function getClasses()
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists(BASE_PATH . '/runtime/container/graphql.cache')) {
            return unserialize($filesystem->get(BASE_PATH . '/runtime/container/graphql.cache'));
        }
        return self::$classes;
    }
}
