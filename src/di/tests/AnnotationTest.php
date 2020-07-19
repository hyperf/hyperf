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
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\ClassLoader;
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
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testIgnoreAnnotations()
    {
        BetterReflectionManager::initClassReflector([__DIR__ . '/Stub/']);

        $scaner = new Scanner($loader = Mockery::mock(ClassLoader::class), new ScanConfig(false, '/'));
        $reader = new AnnotationReader();
        $scaner->collect($reader, $ref = BetterReflectionManager::reflectClass(Ignore::class));
        $annotations = AnnotationCollector::get(Ignore::class . '._c');
        $this->assertArrayHasKey(IgnoreDemoAnnotation::class, $annotations);

        AnnotationCollector::clear();

        $scaner = new Scanner($loader, new ScanConfig(false, '/', [], [], ['IgnoreDemoAnnotation']));
        $reader = new AnnotationReader();
        $scaner->collect($reader, $ref);
        $annotations = AnnotationCollector::get(Ignore::class . '._c');
        $this->assertNull($annotations);
    }
}
