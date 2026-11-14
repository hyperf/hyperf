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

use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\Aspect\CacheEvictAspect;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Cache\Driver\KeyCollectorInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CacheEvictAspectTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testProcessEvictsAllCachesInOrder(): void
    {
        $events = [];
        $processCount = 0;

        $annotationManager = Mockery::mock(AnnotationManager::class);
        $annotationManager->shouldReceive('getCacheEvictValues')->once()->andReturn([
            ['user:7', false, 'first', new CacheEvict('user')],
            ['role:7', false, 'second', new CacheEvict('role')],
        ]);

        $firstDriver = Mockery::mock(DriverInterface::class);
        $firstDriver->shouldReceive('delete')->once()->with('user:7')->andReturnUsing(function () use (&$events) {
            $events[] = 'delete:user:7';
            return true;
        });
        $secondDriver = Mockery::mock(DriverInterface::class);
        $secondDriver->shouldReceive('delete')->once()->with('role:7')->andReturnUsing(function () use (&$events) {
            $events[] = 'delete:role:7';
            return true;
        });

        $manager = Mockery::mock(CacheManager::class);
        $manager->shouldReceive('getDriver')->once()->with('first')->andReturn($firstDriver);
        $manager->shouldReceive('getDriver')->once()->with('second')->andReturn($secondDriver);

        $point = new ProceedingJoinPoint(function () use (&$events, &$processCount) {
            ++$processCount;
            $events[] = 'process';
            return 'result';
        }, 'Example', 'update', ['keys' => ['id' => 7]]);
        $point->pipe = static function (ProceedingJoinPoint $point) {
            return $point->processOriginalMethod();
        };

        $result = (new CacheEvictAspect($manager, $annotationManager))->process($point);

        $this->assertSame('result', $result);
        $this->assertSame(1, $processCount);
        $this->assertSame(['delete:user:7', 'delete:role:7', 'process'], $events);
    }

    public function testProcessClearsPrefix(): void
    {
        $annotationManager = Mockery::mock(AnnotationManager::class);
        $annotationManager->shouldReceive('getCacheEvictValues')->once()->andReturn([
            ['user:', true, 'default', new CacheEvict('user', all: true)],
        ]);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('clearPrefix')->once()->with('user:')->andReturnTrue();
        $driver->shouldNotReceive('delete');

        $manager = Mockery::mock(CacheManager::class);
        $manager->shouldReceive('getDriver')->once()->with('default')->andReturn($driver);

        $point = $this->createProceedingJoinPoint();
        $this->assertSame('result', (new CacheEvictAspect($manager, $annotationManager))->process($point));
    }

    public function testProcessDeletesCollectedKeys(): void
    {
        $annotationManager = Mockery::mock(AnnotationManager::class);
        $annotationManager->shouldReceive('getCacheEvictValues')->once()->andReturn([
            ['user:', true, 'default', new CacheEvict('user', all: true, collect: true)],
        ]);

        $driver = Mockery::mock(DriverInterface::class . ', ' . KeyCollectorInterface::class);
        $driver->shouldReceive('keys')->once()->ordered()->with('userMEMBERS')->andReturn(['user:1', 'user:2']);
        $driver->shouldReceive('deleteMultiple')->once()->ordered()->with(['user:1', 'user:2'])->andReturnTrue();
        $driver->shouldReceive('delete')->once()->ordered()->with('userMEMBERS')->andReturnTrue();
        $driver->shouldNotReceive('clearPrefix');

        $manager = Mockery::mock(CacheManager::class);
        $manager->shouldReceive('getDriver')->once()->with('default')->andReturn($driver);

        $point = $this->createProceedingJoinPoint();
        $this->assertSame('result', (new CacheEvictAspect($manager, $annotationManager))->process($point));
    }

    public function testProcessStopsOnMiddleFailure(): void
    {
        $events = [];
        $processCount = 0;
        $exception = new RuntimeException('cache delete failed');

        $annotationManager = Mockery::mock(AnnotationManager::class);
        $annotationManager->shouldReceive('getCacheEvictValues')->once()->andReturn([
            ['user:7', false, 'first', new CacheEvict('user')],
            ['role:7', false, 'second', new CacheEvict('role')],
            ['permission:7', false, 'third', new CacheEvict('permission')],
        ]);

        $firstDriver = Mockery::mock(DriverInterface::class);
        $firstDriver->shouldReceive('delete')->once()->with('user:7')->andReturnUsing(function () use (&$events) {
            $events[] = 'delete:user:7';
            return true;
        });
        $secondDriver = Mockery::mock(DriverInterface::class);
        $secondDriver->shouldReceive('delete')->once()->with('role:7')->andReturnUsing(function () use (&$events, $exception) {
            $events[] = 'delete:role:7';
            throw $exception;
        });

        $manager = Mockery::mock(CacheManager::class);
        $manager->shouldReceive('getDriver')->once()->ordered()->with('first')->andReturn($firstDriver);
        $manager->shouldReceive('getDriver')->once()->ordered()->with('second')->andReturn($secondDriver);
        $manager->shouldNotReceive('getDriver')->with('third');

        $point = new ProceedingJoinPoint(function () use (&$processCount) {
            ++$processCount;
            return 'result';
        }, 'Example', 'update', ['keys' => ['id' => 7]]);
        $point->pipe = static function (ProceedingJoinPoint $point) {
            return $point->processOriginalMethod();
        };

        try {
            (new CacheEvictAspect($manager, $annotationManager))->process($point);
            $this->fail('The cache exception was not thrown.');
        } catch (RuntimeException $throwable) {
            $this->assertSame($exception, $throwable);
        }
        $this->assertSame(['delete:user:7', 'delete:role:7'], $events);
        $this->assertSame(0, $processCount);
    }

    private function createProceedingJoinPoint(): ProceedingJoinPoint
    {
        $point = new ProceedingJoinPoint(fn () => 'result', 'Example', 'update', ['keys' => ['id' => 7]]);
        $point->pipe = static function (ProceedingJoinPoint $point) {
            return $point->processOriginalMethod();
        };
        return $point;
    }
}
