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
}
