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
namespace Hyperf\Di\ScanHandler;

use Hyperf\Di\Exception\Exception;

class PcntlScanHandler implements ScanHandlerInterface
{
    public function scan(): Scanned
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception('The process fork failed');
        }
        if ($pid) {
            pcntl_wait($status);
            return new Scanned(true);
        }

        return new Scanned(false);
    }
}
