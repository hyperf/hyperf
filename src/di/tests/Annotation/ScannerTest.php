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

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use Hyperf\Support\Composer;
use Hyperf\Support\Filesystem\Filesystem;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\Aspect\Debug1Aspect;
use HyperfTest\Di\Stub\Aspect\Debug2Aspect;
use HyperfTest\Di\Stub\Aspect\Debug3Aspect;
use HyperfTest\Di\Stub\AspectCollector;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class ScannerTest extends TestCase
{
    protected function tearDown(): void
    {
        AspectCollector::clear();
        AnnotationCollector::clear();
        Mockery::close();
        ReflectionManager::clear();
        (function () {
            self::$classLoader = null;
        })->call(new Composer());
    }

    public function testGetChangedAspects()
    {
        $this->getContainer();

        $loader = Mockery::mock(\Composer\Autoload\ClassLoader::class);
        $loader->shouldReceive('findFile')->andReturnUsing(function ($class) {
            return $class;
        });
        Composer::setLoader($loader);

        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $ref = new ReflectionClass($scanner);
        $property = $ref->getProperty('filesystem');
        $property->setAccessible(true);
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
        $method->setAccessible(true);

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

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);
        return $container;
    }
}
