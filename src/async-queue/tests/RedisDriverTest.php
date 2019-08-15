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
use Hyperf\Utils\Str;
use HyperfTest\AsyncQueue\Stub\DemoJob;
use HyperfTest\AsyncQueue\Stub\DemoModel;
use HyperfTest\AsyncQueue\Stub\DemoModelMeta;
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
        $container = $this->getContainer();
        $packer = $container->get(PhpSerializerPacker::class);
        $driver = new RedisDriver($container, [
            'channel' => 'test',
        ]);

        $id = uniqid();
        $driver->push(new DemoJob($id));
        /** @var Message $class */
        $class = $packer->unpack((string) Context::get('test.async-queue.lpush.value'));
        $this->assertSame($id, $class->job()->id);
        $key = Context::get('test.async-queue.lpush.key');
        $this->assertSame('test:waiting', $key);

        $id = uniqid();
        $driver->push(new DemoJob($id), 5);
        /** @var Message $class */
        $class = $packer->unpack((string) Context::get('test.async-queue.zadd.value'));
        $this->assertSame($id, $class->job()->id);
        $key = Context::get('test.async-queue.zadd.key');
        $this->assertSame('test:delayed', $key);
        $time = Context::get('test.async-queue.zadd.delay');
        $this->assertSame(time() + 5, $time);
    }

    public function testDemoModelGenerate()
    {
        $content = Str::random(1000);

        $model = new DemoModel(1, 'Hyperf', 1, $content);
        $s1 = serialize($model);
        $this->assertSame(1128, strlen($s1));

        $meta = $model->compress();
        $s2 = serialize($meta);
        $this->assertSame(65, strlen($s2));
        $this->assertInstanceOf(DemoModelMeta::class, $meta);

        $model2 = $meta->uncompress();
        $this->assertEquals($model, $model2);
    }

    public function testAsyncQueueJobGenerate()
    {
        $container = $this->getContainer();
        $packer = $container->get(PhpSerializerPacker::class);
        $driver = new RedisDriver($container, [
            'channel' => 'test',
        ]);

        $id = uniqid();
        $content = Str::random(1000);
        $model = new DemoModel(1, 'Hyperf', 1, $content);
        $driver->push(new DemoJob($id, $model));

        $serialized = (string) Context::get('test.async-queue.lpush.value');
        $this->assertSame(236, strlen($serialized));

        /** @var Message $class */
        $class = $packer->unpack($serialized);

        $this->assertSame($id, $class->job()->id);
        $this->assertEquals($model, $class->job()->model);

        $key = Context::get('test.async-queue.lpush.key');
        $this->assertSame('test:waiting', $key);

        $this->assertSame(true, $class->attempts());
        $this->assertSame(false, $class->attempts());
    }

    protected function getContainer()
    {
        $packer = new PhpSerializerPacker();
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn($packer);
        $container->shouldReceive('get')->once()->with(EventDispatcherInterface::class)->andReturn(null);
        $container->shouldReceive('get')->once()->with(\Redis::class)->andReturn(new Redis());

        return $container;
    }
}
