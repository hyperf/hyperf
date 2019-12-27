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

use Hyperf\Constants\Exception\ConstantsException;
use Hyperf\Utils\Str;

abstract class AbstractConstants
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

        $message = ConstantsCollector::getValue($class, $code, $name);

        array_shift($arguments);

        if (count($arguments) > 0) {
            return sprintf($message, ...$arguments);
        }

        return $message;
    }
}
