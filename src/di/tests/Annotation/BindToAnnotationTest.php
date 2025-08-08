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

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\BindTo;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use HyperfTest\Di\Stub\Bind\TestBindToClass;
use HyperfTest\Di\Stub\Bind\TestServiceInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class BindToAnnotationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        ReflectionManager::clear();
        AnnotationCollector::clear();
    }

    public function testBindToAnnotation()
    {
        $bindTo = new BindTo(TestServiceInterface::class);
        $this->assertSame(TestServiceInterface::class, $bindTo->getValue());
    }

    public function testBindToAnnotationCollection()
    {
        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $reader = new AnnotationReader();

        // Clear previous collections
        AnnotationCollector::clear();

        // Scan the test class with BindTo annotation
        $scanner->collect($reader, ReflectionManager::reflectClass(TestBindToClass::class));

        // Get collected annotations
        $annotations = AnnotationCollector::getClassesByAnnotation(BindTo::class);

        $this->assertNotEmpty($annotations);
        $this->assertArrayHasKey(TestBindToClass::class, $annotations);

        $metadata = $annotations[TestBindToClass::class];
        $this->assertInstanceOf(MultipleAnnotation::class, $metadata);
        $bindToAnnotations = $metadata->toAnnotations();

        $this->assertCount(1, $bindToAnnotations);
        $this->assertInstanceOf(BindTo::class, $bindToAnnotations[0]);
        $this->assertSame(TestServiceInterface::class, $bindToAnnotations[0]->getValue());
    }

    public function testBindToAnnotationWithStringValue()
    {
        $bindTo = new BindTo('SomeInterface');
        $this->assertSame('SomeInterface', $bindTo->getValue());
    }

    public function testBindToAnnotationWithEmptyValue()
    {
        // Empty string is valid for BindTo annotation
        $bindTo = new BindTo('');
        $this->assertSame('', $bindTo->getValue());
    }
}
