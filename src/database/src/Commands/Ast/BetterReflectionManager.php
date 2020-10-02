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
namespace Hyperf\Database\Commands\Ast;

use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\TypesFinder\FindReturnType;

class BetterReflectionManager
{
    /**
     * @var ClassReflector
     */
    protected static $reflector;

    /**
     * @var FindReturnType
     */
    protected static $return;

    public static function getReturnFinder(): FindReturnType
    {
        if (static::$return instanceof FindReturnType) {
            return static::$return;
        }

        return static::$return = new FindReturnType();
    }

    public static function getReflector(): ClassReflector
    {
        if (self::$reflector instanceof ClassReflector) {
            return self::$reflector;
        }

        return self::$reflector = (new BetterReflection())->classReflector();
    }
}
