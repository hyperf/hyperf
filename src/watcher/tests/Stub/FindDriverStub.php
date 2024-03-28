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

use Hyperf\Watcher\Driver\FindDriver;

class FindDriverStub extends FindDriver
{
    protected function scan(array $fileModifyTimes, string $minutes): array
    {
        return [[], ['.env']];
    }
}
