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

class NullScanHandler implements ScanHandlerInterface
{
    public function scan(): Scanned
    {
        return new Scanned(true);
    }
}
