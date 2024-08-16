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

namespace HyperfTest\Di\Annotation;

use Composer\Autoload\ClassLoader;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use Hyperf\Support\Composer;
use Hyperf\Support\Filesystem\Filesystem;
use HyperfTest\Di\Stub\Aspect\Debug1Aspect;
use HyperfTest\Di\Stub\Aspect\Debug2Aspect;
use HyperfTest\Di\Stub\Aspect\Debug3Aspect;
use HyperfTest\Di\Stub\Collect\Annotation\ClassAnnotation;
use HyperfTest\Di\Stub\Collect\Annotation\ClassConstantAnnotation;
use HyperfTest\Di\Stub\Collect\Annotation\MethodAnnotation;
use HyperfTest\Di\Stub\Collect\Annotation\PropertyAnnotation;
use HyperfTest\Di\Stub\Collect\Foo;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ScannerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        ReflectionManager::clear();
        (fn () => self::$classLoader = null)->call(new Composer());
    }

    public function testGetChangedAspects()
    {
        $this->getContainer();

        $loader = Mockery::mock(ClassLoader::class);
        $loader->shouldReceive('findFile')->andReturnUsing(fn ($class) => $class);
        Composer::setLoader($loader);

        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $ref = new ReflectionClass($scanner);
        $property = $ref->getProperty('filesystem');
        $property->setValue($scanner, $filesystem = Mockery::mock(Filesystem::class . '[lastModified]'));
        $times = [
            Debug1Aspect::class => 5,
            Debug2Aspect::class => 5,
            Debug3Aspect::class => 5,
        ];
        $filesystem->shouldReceive('lastModified')->andReturnUsing(function ($file) use (&$times) {
            return $times[$file];
        });

        $method = $ref->getMethod('getChangedAspects');

        $reader = new AnnotationReader();
        $scanner->collect($reader, ReflectionManager::reflectClass(Debug2Aspect::class));

        // Don't has aspects.cache or aspects changed.
        [$removed, $changed] = $method->invokeArgs($scanner, [[Debug1Aspect::class, Debug2Aspect::class, Debug3Aspect::class], 0]);
        $this->assertEmpty($removed);
        $this->assertEquals([Debug1Aspect::class, Debug2Aspect::class, Debug3Aspect::class], $changed);

        // Removed aspect, but the aspect has annotation @Aspect.
        [$removed, $changed] = $method->invokeArgs($scanner, [[Debug1Aspect::class, Debug3Aspect::class], 10]);
        $this->assertEmpty($removed);
        $this->assertEmpty($changed);

        // Removed aspect.
        [$removed, $changed] = $method->invokeArgs($scanner, [[Debug3Aspect::class], 10]);
        $this->assertEquals([Debug1Aspect::class], $removed);
        $this->assertEmpty($changed);

        $times[Debug3Aspect::class] = 20;

        // Changed aspect.
        [$removed, $changed] = $method->invokeArgs($scanner, [[Debug3Aspect::class], 10]);
        $this->assertEmpty($removed);
        $this->assertEquals([Debug3Aspect::class], $changed);
    }

    public function testCollect()
    {
        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $reader = new AnnotationReader();
        $scanner->collect($reader, ReflectionManager::reflectClass(Foo::class));

        $classAnnotation = AnnotationCollector::getClassAnnotation(Foo::class, ClassAnnotation::class);
        $this->assertInstanceOf(ClassAnnotation::class, $classAnnotation);

        $methodAnnotation = AnnotationCollector::getClassMethodAnnotation(Foo::class, 'method')[MethodAnnotation::class];
        $this->assertInstanceOf(MethodAnnotation::class, $methodAnnotation);

        $propertyAnnotation = AnnotationCollector::getClassPropertyAnnotation(Foo::class, 'foo')[PropertyAnnotation::class];
        $this->assertInstanceOf(PropertyAnnotation::class, $propertyAnnotation);

        $classConstantAnnotation = AnnotationCollector::getClassConstantAnnotation(Foo::class, 'FOO')[ClassConstantAnnotation::class];
        $this->assertInstanceOf(ClassConstantAnnotation::class, $classConstantAnnotation);

        $result = AnnotationCollector::getClassesByAnnotation(ClassAnnotation::class);
        $this->assertSame([Foo::class => $classAnnotation], $result);

        $result = AnnotationCollector::getMethodsByAnnotation(MethodAnnotation::class);
        $this->assertSame([['class' => Foo::class, 'method' => 'method', 'annotation' => $methodAnnotation]], $result);

        $result = AnnotationCollector::getPropertiesByAnnotation(PropertyAnnotation::class);
        $this->assertSame([['class' => Foo::class, 'property' => 'foo', 'annotation' => $propertyAnnotation]], $result);

        $result = AnnotationCollector::getClassConstantsByAnnotation(ClassConstantAnnotation::class);
        $this->assertSame([['class' => Foo::class, 'constant' => 'FOO', 'annotation' => $classConstantAnnotation]], $result);
    }

    protected function getContainer(): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        return $container;
    }
}
