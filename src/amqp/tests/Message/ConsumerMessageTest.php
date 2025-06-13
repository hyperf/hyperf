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

use Hyperf\Amqp\ConnectionFactory;
use Hyperf\Amqp\Consumer;
use Hyperf\Amqp\Message\Type;
use Hyperf\Contract\StdoutLoggerInterface;
use HyperfTest\Amqp\Stub\ContainerStub;
use HyperfTest\Amqp\Stub\QosConsumer;
use Mockery;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ConsumerMessageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testQosConfig()
    {
        $container = ContainerStub::getContainer();
        $channel = Mockery::mock(AMQPChannel::class);
        $channel->shouldReceive('exchange_declare')->andReturnUsing(function (...$args) {
            $this->assertSame('qos', $args[0]);
            $this->assertSame(Type::TOPIC->value, $args[1]);
            $this->assertSame(9, count($args));
        });
        $channel->shouldReceive('queue_declare')->andReturnUsing(function (...$args) {
            $this->assertSame('qos.rk.queue', $args[0]);
        });
        $channel->shouldReceive('queue_bind')->andReturnUsing(function (...$args) {
            $this->assertSame('qos.rk.queue', $args[0]);
            $this->assertSame('qos', $args[1]);
            $this->assertSame('qos.rk', $args[2]);
        });
        $channel->shouldReceive('basic_qos')->andReturnUsing(function (...$args) {
            $this->assertSame([null, 10, null], $args);
        });

        $consumer = new Consumer($container, $container->get(ConnectionFactory::class), $container->get(StdoutLoggerInterface::class));
        $consumer->declare(new QosConsumer(), $channel);
    }
}
