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

namespace HyperfTest\Amqp;

use HyperfTest\Amqp\Stub\DemoConsumer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MessageTest extends TestCase
{
    public function testMultiRoutingKey()
    {
        $consumer = new DemoConsumer();

        $this->assertSame('hyperf1,hyperf2', $consumer->getRoutingKey());
    }
}
