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

use Hyperf\Cache\Annotation\CacheAhead;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\Aspect\CacheAheadAspect;
use Hyperf\Cache\CacheManager;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use HyperfTest\Cache\Stub\ContainerStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AspectTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCacheAheadAspect()
    {
        $container = ContainerStub::getContainer();
        $manager = Mockery::mock(AnnotationManager::class);
        $manager->shouldReceive('getCacheAheadValue')->andReturn(['test', 3600, 'default', new CacheAhead('test', '1', 3600, 600)]);
        $redis = $container->get(CacheManager::class)->getDriver();
        $redis->delete('test');

        $aspect = new CacheAheadAspect($container->get(CacheManager::class), $manager);
        $closure = static function () {
            return uniqid();
        };
        $point = new ProceedingJoinPoint($closure, 'test', 'test', ['keys' => []]);
        $point->pipe = static function (ProceedingJoinPoint $res) {return uniqid(); };

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

        $data = $redis->get('test');
        $data['expired_time'] = time() - 1;
        $redis->set('test', $data, 86400);

        $redis->delete('test:lock');
        $res = $aspect->process($point);
        $this->assertNotEquals($id, $res);
        $this->assertSame($res, $aspect->process($point));
    }
}
