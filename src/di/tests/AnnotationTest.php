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
namespace HyperfTest\Di;

use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Exception\DirectoryNotExistException;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\Ignore;
use HyperfTest\Di\Stub\IgnoreDemoAnnotation;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testIgnoreAnnotations()
    {
        $scaner = new Scanner($loader = Mockery::mock(ClassLoader::class), new ScanConfig(false, '/'), new NullScanHandler());
        $reader = new AnnotationReader();
        $scaner->collect($reader, $ref = ReflectionManager::reflectClass(Ignore::class));
        $annotations = AnnotationCollector::get(Ignore::class . '._c');
        $this->assertArrayHasKey(IgnoreDemoAnnotation::class, $annotations);

        AnnotationCollector::clear();

        $scaner = new Scanner($loader, new ScanConfig(false, '/', [], [], ['IgnoreDemoAnnotation']), new NullScanHandler());
        $reader = new AnnotationReader();
        $scaner->collect($reader, $ref);
        $annotations = AnnotationCollector::get(Ignore::class . '._c');
        $this->assertNull($annotations);
    }

    public function testScanAnnotationsDirectoryNotExist()
    {
        $scanner = new Scanner($loader = Mockery::mock(ClassLoader::class), new ScanConfig(false, '/'), new NullScanHandler());
        $ref = new \ReflectionClass($scanner);
        $method = $ref->getMethod('normalizeDir');
        $method->setAccessible(true);

        $this->expectException(DirectoryNotExistException::class);
        $method->invokeArgs($scanner, [['/not_exists']]);
    }

    public function testScanAnnotationsDirectoryEmpty()
    {
        $scanner = new Scanner($loader = Mockery::mock(ClassLoader::class), new ScanConfig(false, '/'), new NullScanHandler());
        $ref = new \ReflectionClass($scanner);
        $method = $ref->getMethod('normalizeDir');
        $method->setAccessible(true);

        $this->assertSame([], $method->invokeArgs($scanner, [[]]));
    }
}
