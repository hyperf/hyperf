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
    public function __construct()
    {
        if (! extension_loaded('pcntl')) {
            throw new Exception('Missing pcntl extension.');
        }
        if (extension_loaded('grpc')) {
            $grpcForkSupport = ini_get_all('grpc')['grpc.enable_fork_support']['local_value'];
            $grpcForkSupport = strtolower(trim(str_replace('0', '', $grpcForkSupport)));
            if (in_array($grpcForkSupport, ['', 'off', 'false'], true)) {
                throw new Exception(' Grpc fork support must be enabled before the server starts, please set grpc.enable_fork_support = 1 in your php.ini.');
            }
        }
    }

    public function scan(): Scanned
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception('The process fork failed');
        }
        if ($pid) {
            pcntl_wait($status);
            if ($status !== 0) {
                exit(-1);
            }

            return new Scanned(true);
        }

        return new Scanned(false);
    }
}
