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

use Hyperf\Context\Context;
use Hyperf\Redis\RedisProxy;

class Redis extends RedisProxy
{
    public function __construct()
    {
    }

    public function lPush($key, ...$values)
    {
        Context::set('test.async-queue.lpush.key', $key);
        Context::set('test.async-queue.lpush.value', $values[0]);
        return 1;
    }

    public function zAdd($key, ...$values)
    {
        Context::set('test.async-queue.zadd.key', $key);
        Context::set('test.async-queue.zadd.value', $values[1]);
        Context::set('test.async-queue.zadd.delay', $values[0]);
        return 1;
    }
}
