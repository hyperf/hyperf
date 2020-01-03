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

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    public function testIntCacheableAndCachePut()
    {
        $annotation = new Cacheable([
            'prefix' => 'test',
            'ttl' => 3600,
        ]);

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);

        $annotation = new Cacheable([
            'prefix' => 'test',
            'ttl' => '3600',
        ]);

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);

        $annotation = new CachePut([
            'prefix' => 'test',
            'ttl' => '3600',
            'offset' => '100',
        ]);

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);
        $this->assertSame(100, $annotation->offset);
    }

    public function testAnnotationManager()
    {
        $cacheable = new Cacheable(['prefix' => 'test', 'ttl' => 3600, 'offset' => 100]);
        $cacheable2 = new Cacheable(['prefix' => 'test', 'ttl' => 3600]);
        $cacheput = new CachePut(['prefix' => 'test', 'ttl' => 3600, 'offset' => 100]);
        $config = Mockery::mock(ConfigInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        /** @var AnnotationManager $manager */
        $manager = Mockery::mock(AnnotationManager::class . '[getAnnotation]', [$config, $logger]);
        $manager->shouldAllowMockingProtectedMethods();
        $manager->shouldReceive('getAnnotation')->with(Cacheable::class, Mockery::any(), Mockery::any())->once()->andReturn($cacheable);
        $manager->shouldReceive('getAnnotation')->with(Cacheable::class, Mockery::any(), Mockery::any())->once()->andReturn($cacheable2);
        $manager->shouldReceive('getAnnotation')->with(CachePut::class, Mockery::any(), Mockery::any())->once()->andReturn($cacheput);

        [$key, $ttl] = $manager->getCacheableValue('Foo', 'test', ['id' => $id = uniqid()]);
        $this->assertSame('test:' . $id, $key);
        $this->assertGreaterThanOrEqual(3600, $ttl);
        $this->assertLessThanOrEqual(3700, $ttl);

        [$key, $ttl] = $manager->getCachePutValue('Foo', 'test', ['id' => $id = uniqid()]);
        $this->assertSame('test:' . $id, $key);
        $this->assertGreaterThanOrEqual(3600, $ttl);
        $this->assertLessThanOrEqual(3700, $ttl);

        [$key, $ttl] = $manager->getCacheableValue('Foo', 'test', ['id' => $id = uniqid()]);
        $this->assertSame('test:' . $id, $key);
        $this->assertSame(3600, $ttl);
    }
}
