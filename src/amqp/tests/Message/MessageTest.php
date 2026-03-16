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

namespace HyperfTest\Amqp\Message;

use HyperfTest\Amqp\Stub\DemoConsumer;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MessageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testMultiRoutingKey()
    {
        $consumer = new DemoConsumer();
        $this->assertSame(['hyperf1', 'hyperf2'], $consumer->getRoutingKey());

        $consumer->setRoutingKey('route1');
        $this->assertSame('route1', $consumer->getRoutingKey());

        $consumer->setRoutingKey(['route1', 'route2']);
        $this->assertSame(['route1', 'route2'], $consumer->getRoutingKey());
    }
}
