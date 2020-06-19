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
namespace Hyperf\Constants;

use Hyperf\Di\MetadataCollector;

class ConstantsCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function getValue($className, $code, $key)
    {
        return static::$container[$className][$code][$key] ?? '';
    }

    public static function hasValue($className, $code, $key)
    {
        return isset(static::$container[$className][$code][$key]);
    }

    public static function getMessageToArray($className, ?Format $format = null)
    {
        $format = $format ?: new UnFormat();

        $constants = [];
        foreach (static::get($className, []) as $code => $value) {
            if (!self::hasValue($className, $code, 'message')) {
                continue;
            }

            $tmp = $format->parse($code, self::getValue($className, $code, 'message'));
            if ($format instanceof UnFormat) {
                $constants += $tmp;
            } else {
                $constants = array_merge($constants, $tmp);
            }
        }
        return $constants;
    }
}
