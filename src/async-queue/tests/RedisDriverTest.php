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

namespace HyperfTest\AsyncQueue;

use Hyperf\AsyncQueue\Driver\RedisDriver;
use Hyperf\AsyncQueue\Message;
use Hyperf\Utils\Context;
use Hyperf\Utils\Packer\PhpSerializerPacker;
use HyperfTest\AsyncQueue\Stub\DemoJob;
use HyperfTest\AsyncQueue\Stub\Redis;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class RedisDriverTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testDriverPush()
    {
        $packer = new PhpSerializerPacker();
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->once()->with(PhpSerializerPacker::class)->andReturn($packer);
        $container->shouldReceive('get')->once()->with(EventDispatcherInterface::class)->andReturn(null);
        $container->shouldReceive('get')->once()->with(\Redis::class)->andReturn(new Redis());

        $driver = new RedisDriver($container, [
            'channel' => 'test',
        ]);

        $id = uniqid();
        $driver->push(new DemoJob($id));
        /** @var Message $class */
        $class = $packer->unpack(Context::get('test.async-queue.lpush.value'));
        $this->assertSame($id, $class->job()->id);
        $key = Context::get('test.async-queue.lpush.key');
        $this->assertSame('test:waiting', $key);

        $id = uniqid();
        $driver->push(new DemoJob($id), 5);
        /** @var Message $class */
        $class = $packer->unpack(Context::get('test.async-queue.zadd.value'));
        $this->assertSame($id, $class->job()->id);
        $key = Context::get('test.async-queue.zadd.key');
        $this->assertSame('test:delayed', $key);
        $time = Context::get('test.async-queue.zadd.delay');
        $this->assertSame(time() + 5, $time);
    }
}
