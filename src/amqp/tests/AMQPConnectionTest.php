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
namespace HyperfTest\Amqp;

use HyperfTest\Amqp\Stub\AMQPConnectionStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AMQPConnectionTest extends TestCase
{
    public function testMakeChannelId()
    {
        $connection = new AMQPConnectionStub();

        $this->assertSame(1, $connection->makeChannelId());
        $connection->channels[2] = 1;
        $this->assertSame(3, $connection->makeChannelId());
        $connection->setLastChannelId(10000);
        $this->assertSame(10001, $connection->makeChannelId());
        $connection->setLastChannelId(65534);
        $this->assertSame(65535, $connection->makeChannelId());
        $this->assertSame(1, $connection->makeChannelId());
        $this->assertSame(3, $connection->makeChannelId());
    }
}
