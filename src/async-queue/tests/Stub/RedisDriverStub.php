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

namespace HyperfTest\AsyncQueue\Stub;

use Hyperf\AsyncQueue\Driver\RedisDriver;
use Hyperf\Coroutine\Concurrent;

class RedisDriverStub extends RedisDriver
{
    public function getConcurrent(): ?Concurrent
    {
        return $this->concurrent;
    }
}
