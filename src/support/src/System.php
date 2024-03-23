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

class System
{
    /**
     * Get the number of CPU cores.
     */
    public static function getCpuCoresNum(): int
    {
        if (function_exists('swoole_cpu_num')) {
            return swoole_cpu_num();
        }

        $num = 1;

        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);

            $num = count($matches[0]);
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $process = @popen('wmic cpu get NumberOfCores', 'rb');

            if ($process !== false) {
                fgets($process);
                $num = intval(fgets($process));

                pclose($process);
            }
        } else {
            $process = @popen('sysctl -a', 'rb');

            if ($process !== false) {
                $output = stream_get_contents($process);

                preg_match('/hw.ncpu: (\d+)/', $output, $matches);
                if ($matches) {
                    $num = intval($matches[1][0]);
                }

                pclose($process);
            }
        }

        return $num;
    }
}
