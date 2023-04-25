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
namespace Hyperf\Support;

use Hyperf\Stringable\Str;
use RuntimeException;

class Network
{
    public static function ip(): string
    {
        $ips = [];
        if (function_exists('swoole_get_local_ip')) {
            $ips = swoole_get_local_ip();
        }
        if (empty($ips) && function_exists('net_get_interfaces')) {
            foreach (net_get_interfaces() ?: [] as $name => $value) {
                foreach ($value['unicast'] as $item) {
                    if (! isset($item['address'])) {
                        continue;
                    }
                    if (! Str::contains($item['address'], ':') && $item['address'] !== '127.0.0.1') {
                        $ips[$name] = $item['address'];
                    }
                }
            }
        }
        if (is_array($ips) && ! empty($ips)) {
            return current($ips);
        }

        $name = gethostname();
        if ($name === false) {
            throw new RuntimeException('Can not get the internal IP.');
        }

        return gethostbyname($name);
    }
}
