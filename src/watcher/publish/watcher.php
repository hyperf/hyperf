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
    'driver' => ScanFileDriver::class,
    'bin' => sprintf('%s -d swoole.use_shortname=Off', $_SERVER['_'] ?? 'php'),
    'watch' => [
        'dir' => ['app', 'config'],
        'file' => ['.env'],
        'scan_interval' => 2000,
    ],
];
