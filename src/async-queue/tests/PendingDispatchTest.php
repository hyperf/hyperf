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

namespace HyperfTest\AsyncQueue;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\AsyncQueue\PendingDispatch;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use HyperfTest\AsyncQueue\Stub\DemoJob;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PendingDispatchTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConstructor()
    {
        $job = new DemoJob(1);
        $this->setupContainer($job, 0, 'default');

        $pendingDispatch = new PendingDispatch($job);

        $this->assertInstanceOf(PendingDispatch::class, $pendingDispatch);
    }

    public function testSetMaxAttempts()
    {
        $job = new DemoJob(1);
        $this->setupContainer($job, 0, 'default');

        $pendingDispatch = new PendingDispatch($job);

        $result = $pendingDispatch->setMaxAttempts(5);

        $this->assertSame($pendingDispatch, $result);
        $this->assertSame(5, $job->getMaxAttempts());
    }

    public function testOnPool()
    {
        $job = new DemoJob(1);
        $this->setupContainer($job, 0, 'custom_pool');

        $pendingDispatch = new PendingDispatch($job);

        $result = $pendingDispatch->onPool('custom_pool');

        $this->assertSame($pendingDispatch, $result);
    }

    public function testDelay()
    {
        $job = new DemoJob(1);
        $this->setupContainer($job, 100, 'default');

        $pendingDispatch = new PendingDispatch($job);

        $result = $pendingDispatch->delay(100);

        $this->assertSame($pendingDispatch, $result);
    }

    public function testMethodChaining()
    {
        $job = new DemoJob(1);
        $this->setupContainer($job, 60, 'test_pool');

        $pendingDispatch = new PendingDispatch($job);

        $result = $pendingDispatch
            ->setMaxAttempts(3)
            ->onPool('test_pool')
            ->delay(60);

        $this->assertSame($pendingDispatch, $result);
        $this->assertSame(3, $job->getMaxAttempts());
    }

    public function testDestructorPushesJobToDefaultQueue()
    {
        $job = new DemoJob(123);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 0)
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $pendingDispatch = new PendingDispatch($job);
        unset($pendingDispatch); // Trigger destructor

        // Mock expectations will be verified in tearDown by Mockery::close()
        $this->assertTrue(true);
    }

    public function testDestructorPushesJobToCustomPool()
    {
        $job = new DemoJob(456);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 0)
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('custom_pool')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $pendingDispatch = new PendingDispatch($job);
        $pendingDispatch->onPool('custom_pool');
        unset($pendingDispatch); // Trigger destructor

        $this->assertTrue(true);
    }

    public function testDestructorPushesJobWithDelay()
    {
        $job = new DemoJob(789);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 120)
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $pendingDispatch = new PendingDispatch($job);
        $pendingDispatch->delay(120);
        unset($pendingDispatch); // Trigger destructor

        $this->assertTrue(true);
    }

    public function testDestructorWithAllOptions()
    {
        $job = new DemoJob(999);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 300)
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('priority_pool')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $pendingDispatch = new PendingDispatch($job);
        $pendingDispatch
            ->setMaxAttempts(10)
            ->onPool('priority_pool')
            ->delay(300);

        $this->assertSame(10, $job->getMaxAttempts());

        unset($pendingDispatch); // Trigger destructor

        $this->assertTrue(true);
    }

    public function testConditionableWhen()
    {
        $job = new DemoJob(111);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 50) // The callback sets delay to 50
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $pendingDispatch = new PendingDispatch($job);

        $result = $pendingDispatch->when(true, function ($pending) {
            return $pending->delay(50);
        });

        $this->assertSame($pendingDispatch, $result);

        unset($pendingDispatch); // Trigger destructor

        $this->assertTrue(true);
    }

    public function testConditionableWhenFalse()
    {
        $job = new DemoJob(222);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 0) // Should be 0 since when condition is false
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $pendingDispatch = new PendingDispatch($job);
        $pendingDispatch->when(false, function ($pending) {
            return $pending->delay(50);
        });

        unset($pendingDispatch); // Trigger destructor

        $this->assertTrue(true);
    }

    public function testConditionableUnless()
    {
        $job = new DemoJob(333);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 75) // The callback sets delay to 75
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('default')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $pendingDispatch = new PendingDispatch($job);

        $result = $pendingDispatch->unless(false, function ($pending) {
            return $pending->delay(75);
        });

        $this->assertSame($pendingDispatch, $result);

        unset($pendingDispatch); // Trigger destructor

        $this->assertTrue(true);
    }

    public function testConditionableChaining()
    {
        $job = new DemoJob(444);

        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, 100)
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with('conditional_pool')
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);

        $isHighPriority = true;
        $hasDelay = true;

        $pendingDispatch = new PendingDispatch($job);
        $pendingDispatch
            ->setMaxAttempts(5)
            ->when($isHighPriority, function ($pending) {
                return $pending->onPool('conditional_pool');
            })
            ->unless(! $hasDelay, function ($pending) {
                return $pending->delay(100);
            });

        $this->assertSame(5, $job->getMaxAttempts());

        unset($pendingDispatch); // Trigger destructor

        $this->assertTrue(true);
    }

    protected function setupContainer(DemoJob $job, int $delay, string $pool): void
    {
        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('push')
            ->once()
            ->with($job, $delay)
            ->andReturn(true);

        $driverFactory = Mockery::mock(DriverFactory::class);
        $driverFactory->shouldReceive('get')
            ->once()
            ->with($pool)
            ->andReturn($driver);

        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->once()
            ->with(DriverFactory::class)
            ->andReturn($driverFactory);

        ApplicationContext::setContainer($container);
    }
}
