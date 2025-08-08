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
use Hyperf\Di\Annotation\Bind;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\NullScanHandler;
use HyperfTest\Di\Stub\Bind\TestBind;
use HyperfTest\Di\Stub\Bind\TestBindClass;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class BindAnnotationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        ReflectionManager::clear();
        AnnotationCollector::clear();
    }

    public function testBindAnnotation()
    {
        $bind = new Bind('test.service');
        $this->assertSame('test.service', $bind->getValue());
    }

    public function testBindAnnotationCollection()
    {
        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $reader = new AnnotationReader();

        // Scan the test class with Bind annotation
        $scanner->collect($reader, ReflectionManager::reflectClass(TestBindClass::class));

        // Get collected annotations
        $annotations = AnnotationCollector::getClassesByAnnotation(Bind::class);

        $this->assertNotEmpty($annotations);
        $this->assertArrayHasKey(TestBindClass::class, $annotations);
        /**
         * @var MultipleAnnotation $metadata
         */
        $metadata = $annotations[TestBindClass::class];
        $this->assertSame(Bind::class, $metadata->className());
        $bindAnnotations = $metadata->toAnnotations();
        $this->assertCount(1, $bindAnnotations);
        $this->assertInstanceOf(Bind::class, $bindAnnotations[0]);
        $this->assertSame(TestBind::class, $bindAnnotations[0]->getValue());
    }

    public function testBindAnnotationWithEmptyValue()
    {
        // Empty string is valid for Bind annotation
        $bind = new Bind('');
        $this->assertSame('', $bind->getValue());
    }
}
