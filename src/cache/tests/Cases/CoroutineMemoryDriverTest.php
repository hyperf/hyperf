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

namespace HyperfTest\Cache\Cases;

use Hyperf\Cache\Driver\CoroutineMemoryDriver;
use Hyperf\Utils\Packer\PhpSerializerPacker;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class CoroutineMemoryDriverTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testCacheableOnlyInSameCoroutine()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn(new PhpSerializerPacker());

        $driver = new CoroutineMemoryDriver($container, []);
        $this->assertSame(null, $driver->get('test', null));
        $driver->set('test', 'xxx');
        $this->assertSame('xxx', $driver->get('test', null));

        parallel([function () use ($driver) {
            $this->assertSame(null, $driver->get('test', null));
            $driver->set('test', 'xxx2');
            $this->assertSame('xxx2', $driver->get('test', null));
        }]);
    }
}
