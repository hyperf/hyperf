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
putenv('SCAN_CACHEABLE=(true)');

$dir = dirname(dirname(__DIR__));
$cwd = getcwd();

switch (true) {
    case file_exists($dir . '/bin/hyperf.php'):
        require_once $dir . '/bin/hyperf.php';
        break;
    case file_exists($cwd . '/bin/hyperf.php'):
        require_once $cwd . '/bin/hyperf.php';
        break;
    default:
        require_once 'bin/hyperf.php';
        break;
}
