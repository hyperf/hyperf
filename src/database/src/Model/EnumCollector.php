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

namespace Hyperf\Database\Model;

use BackedEnum;
use ReflectionEnum;
use UnitEnum;

class EnumCollector
{
    /**
     * @var array<null|ReflectionEnum>
     */
    protected static array $reflections = [];

    public static function get(string $class): ReflectionEnum
    {
        if (isset(static::$reflections[$class])) {
            return static::$reflections[$class];
        }

        return static::$reflections[$class] = new ReflectionEnum($class);
    }

    public static function has(string $class): bool
    {
        return isset(static::$reflections[$class]);
    }

    public static function getEnumCaseFromValue(string $class, int|string $value): BackedEnum|UnitEnum
    {
        $ref = self::get($class);
        if ($ref->isBacked()) {
            if ($ref->getBackingType()?->getName() === 'int') {
                return $class::from((int) $value);
            }

            return $class::from((string) $value);
        }

        return constant($class . '::' . $value);
    }
}
