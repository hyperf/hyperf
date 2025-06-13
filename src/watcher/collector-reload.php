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
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

$basePath = getcwd();
$dir = dirname(__DIR__, 3);

if (file_exists($dir . '/vendor/autoload.php')) {
    $basePath = $dir;
}

! defined('SWOOLE_HOOK_ALL') && define('SWOOLE_HOOK_ALL', 0);
! defined('BASE_PATH') && define('BASE_PATH', $basePath);
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require BASE_PATH . '/vendor/autoload.php';

use Hyperf\Watcher\Process;

$process = new Process($argv[1]);
$process();
