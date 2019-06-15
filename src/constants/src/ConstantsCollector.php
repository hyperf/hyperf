<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Constants;

use Hyperf\Utils\Traits\Container;

class ConstantsCollector
{
    use Container;

    /**
     * @var array
     */
    protected static $container = [];

    public static function getValue($className, $code, $key)
    {
        return static::$container[$className][$code][$key] ?? '';
    }
}
