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

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheAhead;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotationInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AnnotationTest extends TestCase
{
    protected function tearDown(): void
    {
        AnnotationCollector::clear(CacheEvictStub::class);
        Mockery::close();
    }

    public function testCacheEvictCanBeRepeated(): void
    {
        $attributes = (new ReflectionMethod(CacheEvictStub::class, 'handle'))->getAttributes(CacheEvict::class);

        foreach ($attributes as $attribute) {
            $attribute->newInstance()->collectMethod(CacheEvictStub::class, 'handle');
        }

        $annotation = AnnotationCollector::getClassMethodAnnotation(CacheEvictStub::class, 'handle')[CacheEvict::class];
        $this->assertInstanceOf(MultipleAnnotationInterface::class, $annotation);

        $annotations = $annotation->toAnnotations();
        $this->assertCount(2, $annotations);
        $this->assertSame('user', $annotations[0]->prefix);
        $this->assertSame('role', $annotations[1]->prefix);
    }

    public function testCacheEvictValues(): void
    {
        $attributes = (new ReflectionMethod(CacheEvictStub::class, 'handle'))->getAttributes(CacheEvict::class);
        foreach ($attributes as $attribute) {
            $attribute->newInstance()->collectMethod(CacheEvictStub::class, 'handle');
        }
        $singleAttribute = (new ReflectionMethod(CacheEvictStub::class, 'handleSingle'))->getAttributes(CacheEvict::class)[0];
        $singleAttribute->newInstance()->collectMethod(CacheEvictStub::class, 'handleSingle');

        $manager = new AnnotationManager(
            Mockery::mock(ConfigInterface::class),
            Mockery::mock(StdoutLoggerInterface::class)
        );
        $values = $manager->getCacheEvictValues(CacheEvictStub::class, 'handle', ['id' => 7]);

        $this->assertSame('user:user_7', $values[0][0]);
        $this->assertFalse($values[0][1]);
        $this->assertSame('default', $values[0][2]);
        $this->assertSame('user', $values[0][3]->prefix);
        $this->assertSame('role:', $values[1][0]);
        $this->assertTrue($values[1][1]);
        $this->assertSame('secondary', $values[1][2]);
        $this->assertTrue($values[1][3]->collect);
        $this->assertSame($values[0], $manager->getCacheEvictValue(CacheEvictStub::class, 'handle', ['id' => 7]));
        $this->assertSame(
            ['single:7', false, 'default'],
            array_slice($manager->getCacheEvictValue(CacheEvictStub::class, 'handleSingle', ['id' => 7]), 0, 3)
        );
    }

    public function testIntCacheableAndCachePut()
    {
        $annotation = new Cacheable(
            'test',
            ttl: 3600,
        );

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);

        $annotation = new Cacheable(
            'test',
            ttl: 3600,
        );

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);

        $annotation = new CachePut(
            'test',
            ttl: 3600,
            offset: 100,
        );

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(3600, $annotation->ttl);
        $this->assertSame(100, $annotation->offset);

        $annotation = new Cacheable('test');

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(null, $annotation->ttl);

        $annotation = new CachePut('test');

        $this->assertSame('test', $annotation->prefix);
        $this->assertSame(null, $annotation->ttl);
    }

    public function testAnnotationManager()
    {
        $cacheable = new Cacheable('test', ttl: 3600, offset: 100, skipCacheResults: []);
        $cacheable2 = new Cacheable('test', ttl: 3600, skipCacheResults: []);
        $cacheput = new CachePut('test', ttl: 3600, offset: 100, skipCacheResults: []);
        $cacheahead = new CacheAhead('test', ttl: 3600, aheadSeconds: 600, lockSeconds: 20, skipCacheResults: []);

        $config = Mockery::mock(ConfigInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        /** @var AnnotationManager $manager */
        $manager = Mockery::mock(AnnotationManager::class . '[getAnnotation]', [$config, $logger]);
        $manager->shouldAllowMockingProtectedMethods();
        $manager->shouldReceive('getAnnotation')->with(Cacheable::class, Mockery::any(), Mockery::any())->once()->andReturn($cacheable);
        $manager->shouldReceive('getAnnotation')->with(Cacheable::class, Mockery::any(), Mockery::any())->once()->andReturn($cacheable2);
        $manager->shouldReceive('getAnnotation')->with(CachePut::class, Mockery::any(), Mockery::any())->once()->andReturn($cacheput);
        $manager->shouldReceive('getAnnotation')->with(CacheAhead::class, Mockery::any(), Mockery::any())->once()->andReturn($cacheahead);

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

        [$key, $ttl] = $manager->getCacheAheadValue('Foo', 'test', ['id' => $id = uniqid()]);
        $this->assertSame('test:' . $id, $key);
        $this->assertSame(3600, $ttl);
    }
}

class CacheEvictStub
{
    #[CacheEvict(prefix: 'user', value: 'user_#{id}')]
    #[CacheEvict(prefix: 'role', all: true, group: 'secondary', collect: true)]
    public function handle(int $id): void
    {
    }

    #[CacheEvict(prefix: 'single')]
    public function handleSingle(int $id): void
    {
    }
}
