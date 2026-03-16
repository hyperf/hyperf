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

namespace HyperfTest\Cache\Cases;

use Hyperf\Cache\Collector\CoroutineMemory;
use Hyperf\Cache\Driver\CoroutineMemoryDriver;
use Hyperf\Codec\Packer\PhpSerializerPacker;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CoroutineMemoryDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        CoroutineMemory::instance()->clear();
    }

    public function testCacheableOnlyInSameCoroutine()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn(new PhpSerializerPacker());

        $driver = new CoroutineMemoryDriver($container, []);
        $this->assertSame(null, $driver->get('test', null));
        $driver->set('test', 'xxx');
        $this->assertSame('xxx', $driver->get('test', null));

        parallel([
            function () use ($driver) {
                $this->assertSame(null, $driver->get('test', null));
                $driver->set('test', 'xxx2');
                $this->assertSame('xxx2', $driver->get('test', null));
            },
        ]);
    }

    public function testKeyCollectorInterface()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn(new PhpSerializerPacker());

        $driver = new CoroutineMemoryDriver($container, []);
        $driver->addKey('test', 'key1');
        $driver->addKey('test', 'key2');
        $this->assertEquals(['key1', 'key2'], $driver->keys('test'));
        $driver->delKey('test', 'key2');
        $this->assertEquals(['key1'], $driver->keys('test'));

        parallel([
            function () use ($driver) {
                $this->assertEquals([], $driver->keys('test'));
            },
        ]);
    }

    public function testClearPrefix()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn(new PhpSerializerPacker());

        $driver = new CoroutineMemoryDriver($container, []);
        $driver->set('test_key1', '1');
        $driver->set('test_key2', '1');
        $driver->set('key3', '1');

        $res = $driver->getMultiple(['test_key1', 'test_key2', 'key3']);
        $this->assertSame(['test_key1' => '1', 'test_key2' => '1', 'key3' => '1'], $res);
        $driver->clearPrefix('test');
        $res = $driver->getMultiple(['test_key1', 'test_key2', 'key3']);
        $this->assertSame(['test_key1' => null, 'test_key2' => null, 'key3' => '1'], $res);
    }
}
