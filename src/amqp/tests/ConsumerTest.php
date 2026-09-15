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

use Hyperf\Amqp\ConnectionFactory;
use Hyperf\Amqp\Consumer;
use Hyperf\Coroutine\Concurrent;
use Hyperf\Coroutine\Exception\ChannelClosedException;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\Amqp\Stub\AMQPConnectionStub;
use HyperfTest\Amqp\Stub\ContainerStub;
use HyperfTest\Amqp\Stub\Delay2Consumer;
use HyperfTest\Amqp\Stub\DelayConsumer;
use Mockery;
use PhpAmqpLib\Channel\Frame;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class ConsumerTest extends TestCase
{
    public function testConsumerConcurrentLimit()
    {
        $container = ContainerStub::getContainer();
        $consumer = new Consumer($container, Mockery::mock(ConnectionFactory::class), Mockery::mock(LoggerInterface::class));
        $ref = new ReflectionClass($consumer);
        $method = $ref->getMethod('getConcurrent');
        /** @var Concurrent $concurrent */
        $concurrent = $method->invokeArgs($consumer, ['default']);
        $this->assertSame(10, $concurrent->getLimit());

        /** @var Concurrent $concurrent */
        $concurrent = $method->invokeArgs($consumer, ['co']);
        $this->assertSame(5, $concurrent->getLimit());
    }

    public function testConsumerConcurrentLimitExpandedWhenLessThanPrefetchCount()
    {
        $container = ContainerStub::getContainer();
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->once()->withArgs(function (string $message) {
            $this->assertStringContainsString('concurrent.limit[5]', $message);
            $this->assertStringContainsString('prefetch_count[10]', $message);
            $this->assertStringContainsString('automatically expanded to 10', $message);
            return true;
        });

        $consumer = new Consumer($container, Mockery::mock(ConnectionFactory::class), $logger);
        $ref = new ReflectionClass($consumer);
        $method = $ref->getMethod('getConcurrent');
        /** @var Concurrent $concurrent */
        $concurrent = $method->invokeArgs($consumer, ['co', 10]);
        $this->assertSame(10, $concurrent->getLimit());
    }

    public function testConsumerConcurrentLimitExpandedWhenLimitIsOne()
    {
        $container = ContainerStub::getContainer();
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->once()->withArgs(function (string $message) {
            $this->assertStringContainsString('concurrent.limit[1]', $message);
            $this->assertStringContainsString('prefetch_count[10]', $message);
            return true;
        });

        $consumer = new Consumer($container, Mockery::mock(ConnectionFactory::class), $logger);
        $ref = new ReflectionClass($consumer);
        $method = $ref->getMethod('getConcurrent');
        /** @var Concurrent $concurrent */
        $concurrent = $method->invokeArgs($consumer, ['serial', 10]);
        $this->assertSame(10, $concurrent->getLimit());
    }

    public function testConsumerConcurrentLimitNotExpandedWhenGreaterOrEqualPrefetchCount()
    {
        $container = ContainerStub::getContainer();
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->never();

        $consumer = new Consumer($container, Mockery::mock(ConnectionFactory::class), $logger);
        $ref = new ReflectionClass($consumer);
        $method = $ref->getMethod('getConcurrent');
        /** @var Concurrent $concurrent */
        $concurrent = $method->invokeArgs($consumer, ['default', 1]);
        $this->assertSame(10, $concurrent->getLimit());

        $concurrent = $method->invokeArgs($consumer, ['default', 10]);
        $this->assertSame(10, $concurrent->getLimit());
    }

    public function testWaitChannel()
    {
        $connection = new AMQPConnectionStub();
        $invoker = new ClassInvoker($connection);
        $chan = $invoker->channelManager->get(1, true);
        $chan->push($frame = new Frame(1, 1, 0));
        $this->assertSame($frame, $invoker->wait_channel(1));

        $this->expectException(ChannelClosedException::class);
        $chan->close();
        $invoker->wait_channel(1);
    }

    public function testRewriteDelayMessage()
    {
        $consumer = new DelayConsumer();
        $this->assertSame('x-delayed', (new ClassInvoker($consumer))->getDeadLetterExchange());

        $consumer = new Delay2Consumer();
        $this->assertSame('delayed', (new ClassInvoker($consumer))->getDeadLetterExchange());
    }
}
