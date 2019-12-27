<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Amqp\Message;

use HyperfTest\Amqp\Stub\DemoConsumer;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MessageTest extends TestCase
{
    protected function tearDown()
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
