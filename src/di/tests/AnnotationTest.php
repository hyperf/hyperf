<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Di;

use Hyperf\Di\Annotation\Scanner;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\Ignore;
use HyperfTest\Di\Stub\IgnoreDemoAnnotation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    public function testIgnoreAnnotations()
    {
        $scaner = new Scanner([]);
        $scaner->collect([Ignore::class]);
        $annotations = AnnotationCollector::get(Ignore::class . '._c');
        $this->assertArrayHasKey(IgnoreDemoAnnotation::class, $annotations);

        AnnotationCollector::clear();

        $scaner = new Scanner(['IgnoreDemoAnnotation']);
        $scaner->collect([Ignore::class]);
        $annotations = AnnotationCollector::get(Ignore::class . '._c');
        $this->assertNull($annotations);
    }
}
