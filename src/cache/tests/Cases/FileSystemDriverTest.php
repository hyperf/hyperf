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

use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\FileSystemDriver;
use Hyperf\Codec\Packer\PhpSerializerPacker;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Support\Filesystem\Filesystem;
use HyperfTest\Cache\Stub\Foo;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FileSystemDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();
        $driver->clear();

        Mockery::close();
    }

    public function testSetAndGet()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        $this->assertNull($driver->get('xxx', null));
        $this->assertTrue($driver->set('xxx', 'yyy'));
        $this->assertSame('yyy', $driver->get('xxx'));

        $id = uniqid();
        $obj = new Foo($id);
        $driver->set('xxx', $obj);
        $this->assertSame($id, $driver->get('xxx')->id);
    }

    public function testFetch()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        [$bool, $result] = $driver->fetch('xxx');
        $this->assertFalse($bool);
        $this->assertNull($result);
    }

    public function testExpiredTime()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        $driver->set('xxx', 'yyy', 1);
        [$bool, $result] = $driver->fetch('xxx');
        $this->assertTrue($bool);
        $this->assertSame('yyy', $result);

        sleep(2);

        [$bool, $result] = $driver->fetch('xxx');
        $this->assertFalse($bool);
        $this->assertNull($result);
    }

    public function testDelete()
    {
        $container = $this->getContainer();
        $driver = $container->get(CacheManager::class)->getDriver();

        $driver->set('xxx', 'yyy');
        $driver->set('xxx2', 'yyy');
        $driver->set('xxx3', 'yyy');

        $driver->deleteMultiple(['xxx', 'xxx2']);

        $this->assertNull($driver->get('xxx'));
        $this->assertNotNull($driver->get('xxx3'));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $config = new Config([
            'cache' => [
                'default' => [
                    'driver' => FileSystemDriver::class,
                    'packer' => PhpSerializerPacker::class,
                    'prefix' => 'c:',
                ],
            ],
        ]);

        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive(Mockery::any())->andReturn(null);

        $container->shouldReceive('get')->with(CacheManager::class)->andReturn(new CacheManager($config, $logger));
        $container->shouldReceive('get')->with(Filesystem::class)->andReturn(new Filesystem());
        $container->shouldReceive('make')->with(FileSystemDriver::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new FileSystemDriver($container, $args['config']);
        });
        $container->shouldReceive('get')->with(PhpSerializerPacker::class)->andReturn(new PhpSerializerPacker());

        ApplicationContext::setContainer($container);

        return $container;
    }
}
