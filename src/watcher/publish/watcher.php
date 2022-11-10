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
use Hyperf\Watcher\Driver\ScanFileDriver;

return [
    'bin' => 'php',
    'base_path' => './',
    'driver' => ScanFileDriver::class,
    'pid_file' => 'runtime/hyperf.pid',
    'watch' => [
        'dir' => ['app', 'config'],
        'file' => ['.env'],
        'scan_interval' => 2000,
    ],
];
