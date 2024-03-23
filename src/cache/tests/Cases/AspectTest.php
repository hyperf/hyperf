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
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\Aspect\CacheableAspect;
use Hyperf\Cache\Aspect\CacheAheadAspect;
use Hyperf\Cache\CacheManager;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use HyperfTest\Cache\Stub\ContainerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AspectTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCacheableAspect()
    {
        $container = ContainerStub::getContainer();
        $manager = Mockery::mock(AnnotationManager::class);
        $manager->shouldReceive('getCacheableValue')->andReturn(['test', 3600, 'default', new Cacheable('test', '1', 3600)]);
        $redis = $container->get(CacheManager::class)->getDriver();
        $redis->delete('test');

        $aspect = new CacheableAspect($container->get(CacheManager::class), $manager);
        $point = new ProceedingJoinPoint(fn () => uniqid(), 'test', 'test', ['keys' => []]);
        $point->pipe = static function (ProceedingJoinPoint $res) {
            return $res->processOriginalMethod();
        };
        $id = $aspect->process($point);

        $point->pipe = static function (ProceedingJoinPoint $res) {
            return $res->processOriginalMethod();
        };
        $res = $aspect->process($point);

        $this->assertSame($id, $res);
    }

    public function testCacheableAspectWithSkipCacheResults()
    {
        $container = ContainerStub::getContainer();
        $manager = Mockery::mock(AnnotationManager::class);
        $manager->shouldReceive('getCacheableValue')->andReturn(['test', 3600, 'default', new Cacheable('test', '1', 3600)]);
        $redis = $container->get(CacheManager::class)->getDriver();
        $redis->delete('test');

        $aspect = new CacheableAspect($container->get(CacheManager::class), $manager);
        $point = new ProceedingJoinPoint(fn () => null, 'test', 'test', ['keys' => []]);
        $point->pipe = static function (ProceedingJoinPoint $res) {
            return $res->processOriginalMethod();
        };

        $res = $aspect->process($point);
        $this->assertNull($res);
        $this->assertTrue($redis->has('test'));
        $this->assertNull($redis->get('test'));

        $point->pipe = static function (ProceedingJoinPoint $res) {
            return $res->processOriginalMethod();
        };
        $res = $aspect->process($point);
        $this->assertNull($res);

        $manager = Mockery::mock(AnnotationManager::class);
        $manager->shouldReceive('getCacheableValue')->andReturn(['test', 3600, 'default', new Cacheable('test', '1', 3600, skipCacheResults: [null])]);
        $redis = $container->get(CacheManager::class)->getDriver();
        $redis->delete('test');

        $aspect = new CacheableAspect($container->get(CacheManager::class), $manager);
        $point = new ProceedingJoinPoint(fn () => null, 'test', 'test', ['keys' => []]);
        $point->pipe = static function (ProceedingJoinPoint $res) {
            return $res->processOriginalMethod();
        };

        $res = $aspect->process($point);
        $this->assertNull($res);
        $this->assertFalse($redis->has('test'));

        $point->pipe = static function (ProceedingJoinPoint $res) {
            return 0;
        };

        $res = $aspect->process($point);
        $this->assertSame(0, $res);
        $this->assertTrue($redis->has('test'));
    }

    public function testCacheAheadAspectStoringCacheInCoroutine()
    {
        $container = ContainerStub::getContainer();
        $manager = Mockery::mock(AnnotationManager::class);
        $manager->shouldReceive('getCacheAheadValue')->andReturn(['test2', 3600, 'default', new CacheAhead('test2', '1', 3600, 600)]);
        $redis = $container->get(CacheManager::class)->getDriver();
        $redis->delete('test2');
        /** @var Redis $conn */
        $conn = $redis->getConnection();
        $conn->del('test2:lock');

        $aspect = new CacheAheadAspect($container->get(CacheManager::class), $manager);
        $closure = static function () {
            return uniqid();
        };
        $point = new ProceedingJoinPoint($closure, 'test', 'test', ['keys' => []]);
        $point->pipe = static function (ProceedingJoinPoint $res) {
            return uniqid();
        };

        $id = $aspect->process($point);
        $this->assertSame($id, $aspect->process($point));

        $data = $redis->get('test2');
        $data['expired_time'] = time() - 1;
        $redis->set('test2', $data, 86400);
        $conn->del('test2:lock');

        // Return the old value, then refresh the cache.
        $this->assertSame($id, $aspect->process($point));
        // Return the new value from the cache.
        $this->assertNotEquals($id, $aspect->process($point));
    }

    public function testCacheAheadAspect()
    {
        $container = ContainerStub::getContainer();
        $manager = Mockery::mock(AnnotationManager::class);
        $manager->shouldReceive('getCacheAheadValue')->andReturn(['test', 3600, 'default', new CacheAhead('test', '1', 3600, 600)]);
        $redis = $container->get(CacheManager::class)->getDriver();
        $redis->delete('test');
        /** @var Redis $conn */
        $conn = $redis->getConnection();
        $conn->del('test:lock');

        $aspect = new CacheAheadAspect($container->get(CacheManager::class), $manager);
        $closure = static function () {
            return uniqid();
        };
        $point = new ProceedingJoinPoint($closure, 'test', 'test', ['keys' => []]);
        $point->pipe = static function (ProceedingJoinPoint $res) {
            return uniqid();
        };

        $redis->delete('test:lock');
        $id = $aspect->process($point);
        $redis->delete('test:lock');
        $res = $aspect->process($point);
        $this->assertSame($id, $res);

        $data = $redis->get('test');
        $data['expired_time'] = time() + 1;
        $redis->set('test', $data, 86400);

        $redis->delete('test:lock');
        $res = $aspect->process($point);
        $this->assertSame($id, $res);
    }
}
