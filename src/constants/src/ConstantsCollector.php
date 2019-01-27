<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Constants;

use Hyperf\Utils\Traits\Container;

class ConstantsCollector
{
    use Container;

    public static function getValue($className, $code, $key)
    {
        return static::$container[$className][$code][$key] ?? '';
    }
}
