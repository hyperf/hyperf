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

namespace HyperfTest\Di\Listener;

use Hyperf\Contract\ContainerInterface as ContainerPlusInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\Bind;
use Hyperf\Di\Annotation\BindTo;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Listener\RegisterBindListener;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use Hyperf\Framework\Event\BootApplication;
use HyperfTest\Di\Stub\Bind\TestBindClass;
use HyperfTest\Di\Stub\Bind\TestBindToClass;
use HyperfTest\Di\Stub\Bind\TestMultipleBindClass;
use HyperfTest\Di\Stub\Bind\TestServiceInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RegisterBindListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        ReflectionManager::clear();
        AnnotationCollector::clear();
    }

    public function testListenToBootApplicationEvent()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);

        $listener = new RegisterBindListener($container, $logger);
        $events = $listener->listen();

        $this->assertContains(BootApplication::class, $events);
    }

    public function testProcessWithNonContainerPlusInterface()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);

        // Expect error log when container doesn't implement ContainerPlusInterface
        $logger->shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/Bind registered failed.*ContainerInterface/'));

        $listener = new RegisterBindListener($container, $logger);
        $event = new BootApplication();

        $listener->process($event);
        $this->assertTrue(true);
    }

    public function testProcessRegisterBindAnnotations()
    {
        // Setup container mock with ContainerPlusInterface
        $container = Mockery::mock(ContainerInterface::class, ContainerPlusInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);

        // Setup scanner to collect annotations
        $this->setupAnnotationCollection();

        // Expect define method to be called for Bind annotations
        $container->shouldReceive('define')
            ->once()
            ->with(TestBindClass::class, 'test.service');

        // Expect define method to be called for multiple Bind annotations
        $container->shouldReceive('define')
            ->once()
            ->with(TestMultipleBindClass::class, 'service.primary');
        $container->shouldReceive('define')
            ->once()
            ->with(TestMultipleBindClass::class, 'service.secondary');

        // Expect debug log
        $logger->shouldReceive('debug')
            ->once()
            ->with(Mockery::pattern('/Bind registered by.*RegisterBindListener/'));

        $listener = new RegisterBindListener($container, $logger);
        $event = new BootApplication();

        $listener->process($event);
        $this->assertTrue(true);
    }

    public function testProcessRegisterBindToAnnotations()
    {
        // Setup container mock with ContainerPlusInterface
        $container = Mockery::mock(ContainerInterface::class, ContainerPlusInterface::class);
        $logger = Mockery::mock(StdoutLoggerInterface::class);

        // Setup scanner to collect BindTo annotations
        $this->setupBindToAnnotationCollection();

        // Expect define method to be called for BindTo annotations
        $container->shouldReceive('define')
            ->once()
            ->with(TestServiceInterface::class, TestBindToClass::class);

        // Expect debug log
        $logger->shouldReceive('debug')
            ->once()
            ->with(Mockery::pattern('/Bind registered by.*RegisterBindListener/'));

        $listener = new RegisterBindListener($container, $logger);
        $event = new BootApplication();

        $listener->process($event);
        $this->assertTrue(true);
    }

    public function testProcessWithRealContainer()
    {
        $container = new Container(new DefinitionSource([]));
        $logger = Mockery::mock(StdoutLoggerInterface::class);

        // Setup annotation collection
        $this->setupAnnotationCollection();

        // Expect debug log
        $logger->shouldReceive('debug')
            ->once()
            ->with(Mockery::pattern('/Bind registered by.*RegisterBindListener/'));

        $listener = new RegisterBindListener($container, $logger);
        $event = new BootApplication();

        $listener->process($event);

        // Verify the bindings were registered
        $this->assertTrue($container->has(TestBindClass::class));
        $this->assertTrue($container->has(TestServiceInterface::class));
    }

    protected function setupAnnotationCollection(): void
    {
        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $reader = new AnnotationReader();

        // Clear previous collections
        AnnotationCollector::clear();

        // Collect Bind annotations
        $scanner->collect($reader, ReflectionManager::reflectClass(TestBindClass::class));
        $scanner->collect($reader, ReflectionManager::reflectClass(TestBindToClass::class));
    }

    protected function setupBindToAnnotationCollection(): void
    {
        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $reader = new AnnotationReader();

        // Clear previous collections
        AnnotationCollector::clear();

        // Collect BindTo annotations
        $scanner->collect($reader, ReflectionManager::reflectClass(TestBindToClass::class));
    }
}
