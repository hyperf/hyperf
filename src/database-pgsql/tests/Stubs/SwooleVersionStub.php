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

namespace HyperfTest\Database\PgSQL\Stubs;

use PHPUnit\Framework\Assert;

class SwooleVersionStub
{
    public static function skipV6(): void
    {
        if (self::isV6()) {
            Assert::markTestSkipped('The test is not compatible with swoole 6.0.0 or later.');
        }
    }

    public static function isV6(): bool
    {
        return version_compare(swoole_version(), '6.x', '>=');
    }
}
