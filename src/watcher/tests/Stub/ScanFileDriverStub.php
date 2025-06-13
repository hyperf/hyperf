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

namespace HyperfTest\Watcher\Stub;

use Hyperf\Watcher\Driver\ScanFileDriver;

class ScanFileDriverStub extends ScanFileDriver
{
    protected function getWatchMD5(&$files): array
    {
        $files[] = '.env';
        return ['.env' => md5(strval(microtime()))];
    }
}
