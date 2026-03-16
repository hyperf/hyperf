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

namespace Hyperf\Filesystem;

use League\Flysystem\FilesystemAdapter;

class Version
{
    public static function isV2(): bool
    {
        return interface_exists(FilesystemAdapter::class);
    }
}
