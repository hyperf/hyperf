<?php


namespace Hyperf\Di;


use Roave\BetterReflection\TypesFinder\FindPropertyType;

class TypesFinderManager
{
    /**
     * @var FindPropertyType
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
