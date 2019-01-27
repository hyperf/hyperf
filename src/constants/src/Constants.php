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

use Hyperf\Constants\Exceptions\ConstantsException;
use Hyperf\Utils\Str;

abstract class Constants
{
    public static function __callStatic($name, $arguments)
    {
        if (! Str::startsWith($name, 'get')) {
            throw new ConstantsException('The function is not defined!');
        }

        if (! isset($arguments) || count($arguments) === 0) {
            throw new ConstantsException('The Code is required');
        }

        $code = $arguments[0];
        $name = strtolower(substr($name, 3));
        $class = get_called_class();

        return ConstantsCollector::getValue($class, $code, $name);
    }
}
