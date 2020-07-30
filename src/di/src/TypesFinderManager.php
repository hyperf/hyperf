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
namespace Hyperf\Di;

use Roave\BetterReflection\TypesFinder\FindPropertyType;

class TypesFinderManager
{
    /**
     * @var null|FindPropertyType
     */
    protected static $property;

    public static function getPropertyFinder(): FindPropertyType
    {
        if (static::$property instanceof FindPropertyType) {
            return static::$property;
        }

        return static::$property = new FindPropertyType();
    }
}
