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

use Hyperf\Cache\Collector\Memory;
use Hyperf\Cache\Driver\MemoryDriver;
use Hyperf\Cache\Exception\OverflowException;
use Hyperf\Codec\Packer\PhpSerializerPacker;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class MemoryDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Memory::instance()->clear();
    }

    public function testSetAndGet()
    {
        $driver = new MemoryDriver($this->getContainer(), []);

        $driver->set('test', 'xxx');
        $this->assertSame('xxx', $driver->get('test'));
    }

    public function testSetWithTtl()
    {
        $driver = new MemoryDriver($this->getContainer(), []);

        $driver->set('test', 'xxx', 1);
        $this->assertSame('xxx', $driver->get('test'));

        sleep(3);

        $this->assertNull($driver->get('test'));
    }

    public function testSetWithSize()
    {
        $driver = new MemoryDriver($this->getContainer(), ['size' => 1, 'throw_when_size_exceeded' => true]);

        $driver->set('test1', 'xxx');
        $this->assertSame('xxx', $driver->get('test1'));

        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('The memory cache is full!');
        $driver->set('test2', 'xxx');
    }

    public function testSetWithSizeAndThrowWhenSizeExceededIsFalse()
    {
        $driver = new MemoryDriver($this->getContainer(), ['size' => 1, 'throw_when_size_exceeded' => false]);

        $driver->set('test1', 'xxx');
        $this->assertSame('xxx', $driver->get('test1'));

        $this->assertFalse($driver->set('test2', 'xxx'));
    }

    private function getContainer(): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn(new PhpSerializerPacker());
        return $container;
    }
}
