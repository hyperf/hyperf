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
namespace Hyperf\Utils\CodeGen;

use Jean85\PrettyVersions;

class Package
{
    public static function getPrettyVersion(string $package): string
    {
        try {
            return (string) PrettyVersions::getVersion($package);
        } catch (\Throwable $exception) {
            return 'unknown';
        }
    }
}
