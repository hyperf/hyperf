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

namespace Hyperf\HttpAuth;

/**
 * Class Config.
 */
class Config
{
    /**
     * @var array
     */
    protected static $annotations = [];

    /**
     * @param string $name
     * @param string $value
     * @param string $abstract
     */
    public static function setAnnotation($name, $value, $abstract)
    {
        self::$annotations[$abstract][$name] = $value;
    }

    /**
     * @param $name
     * @param $abstract
     * @return string
     */
    public static function getAnnotation($name, $abstract)
    {
        return self::$annotations[$abstract][$name] ?? '';
    }

    /**
     * @return array
     */
    public static function annotations()
    {
        return self::$annotations;
    }
}
