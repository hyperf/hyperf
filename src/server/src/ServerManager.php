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

namespace Hyperf\Server;

use Hyperf\Support\Traits\Container;

class ServerManager
{
    use Container;

    /**
     * @param array $value [$serverType, $server]
     */
    public static function add(string $name, array $value)
    {
        self::set($name, $value);
    }
}
