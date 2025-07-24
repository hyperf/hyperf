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
            if(self::isWSL2()){
                unset($ips["lo"]);
            }
            return current($ips);
        }

        $name = gethostname();
        if ($name === false) {
            throw new RuntimeException('Can not get the internal IP.');
        }

        return gethostbyname($name);
    }

    /**
     * 获取当前是否wsl2环境
     * @return bool
     */
    private static function isWSL2(): bool
    {
        $version = @file_get_contents('/proc/version');
        $osRelease = @file_get_contents('/proc/sys/kernel/osrelease');
        if ($version && stripos($version, 'Microsoft') !== false) {
            // WSL2 通常会包含 "WSL2" 或类似字段
            if ($osRelease && stripos($osRelease, 'WSL2') !== false) {
                return true;
            }
        }
        return false;
    }
}
