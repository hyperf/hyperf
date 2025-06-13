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

use function Hyperf\Support\env;

class ProcScanHandler implements ScanHandlerInterface
{
    public const SCAN_PROC_WORKER = 'SCAN_PROC_WORKER';

    protected string $bin;

    protected string $stub;

    public function __construct(?string $bin = null, ?string $stub = null)
    {
        if ($bin === null) {
            $bin = PHP_BINARY;
        }

        if ($stub === null) {
            $stub = BASE_PATH . '/bin/hyperf.php';
        }

        $this->bin = $bin;
        $this->stub = $stub;
    }

    public function scan(): Scanned
    {
        if (env(static::SCAN_PROC_WORKER)) {
            return new Scanned(false);
        }

        $proc = proc_open(
            [$this->bin, $this->stub],
            [0 => STDIN, 1 => ['pipe', 'w'], 2 => ['redirect', 1]],
            $pipes,
            null,
            [static::SCAN_PROC_WORKER => '(true)']
        );

        $output = '';
        do {
            $output .= fread($pipes[1], 8192);
        } while (! feof($pipes[1]));

        if (proc_close($proc) !== 0) {
            echo $output;
            exit(-1);
        }

        return new Scanned(true);
    }
}
