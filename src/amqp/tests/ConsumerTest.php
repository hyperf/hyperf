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

use Hyperf\Amqp\Consumer;
use Hyperf\Amqp\Pool\PoolFactory;
use Hyperf\Utils\Coroutine\Concurrent;
use HyperfTest\Amqp\Stub\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @coversNothing
 */
class ConsumerTest extends TestCase
{
    public function testConsumerConcurrentLimit()
    {
        $container = ContainerStub::getContainer();
        $consumer = new Consumer($container, Mockery::mock(PoolFactory::class), Mockery::mock(LoggerInterface::class));
        $ref = new \ReflectionClass($consumer);
        $method = $ref->getMethod('getConcurrent');
        $method->setAccessible(true);
        /** @var Concurrent $concurrent */
        $concurrent = $method->invokeArgs($consumer, ['default']);
        $this->assertSame(10, $concurrent->getLimit());

        /** @var Concurrent $concurrent */
        $concurrent = $method->invokeArgs($consumer, ['co']);
        $this->assertSame(5, $concurrent->getLimit());
    }
}
