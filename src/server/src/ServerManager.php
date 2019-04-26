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

namespace Hyperf\Server;

use Hyperf\Utils\Traits\Container;

class ServerManager
{
    use Container;

    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @param string $name
     * @param array $value [$serverType, $server]
     */
    public static function add(string $name, array $value)
    {
        self::set($name, $value);
    }
}
