<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Redis;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RedisTest extends TestCase
{
    public function testRedisConnect()
    {
        $redis = new \Redis();
        $class = new \ReflectionClass($redis);
        $params = $class->getMethod('connect')->getParameters();
        [$host, $port, $timeout, $retryInterval] = $params;
        $this->assertSame('host', $host->getName());
        $this->assertSame('port', $port->getName());
        $this->assertSame('timeout', $timeout->getName());
        $this->assertSame('retry_interval', $retryInterval->getName());

        $this->assertTrue($redis->connect('127.0.0.1', 6379, 0.0));
    }
}
