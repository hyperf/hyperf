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
use HyperfTest\Di\Stub\FooWithNotExistAnnotation;
use HyperfTest\Di\Stub\IgnoreDemoAnnotation;
use HyperfTest\Di\Stub\NotFoundAttributeTarget;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AnnotationReaderTest extends TestCase
{
    public function testGetNotFoundAttributesOfClass()
    {
        $reflectionClass = new ReflectionClass(NotFoundAttributeTarget::class);

        $exceptionMessage = "No attribute class found for 'HyperfTest\\Di\\Stub\\NotExistAttribute' in HyperfTest\\Di\\Stub\\NotFoundAttributeTarget";

        try {
            $annotationReader = new AnnotationReader();
            $annotationReader->getAttributes($reflectionClass);
        } catch (Throwable $exception) {
        } finally {
            $actual = '';
            if (isset($exception)) {
                $actual = $exception->getMessage();
            }
            $this->assertSame($exceptionMessage, $actual);
        }
    }

    public function testGetNotFoundAttributesOfMethod()
    {
        $reflectionClass = new ReflectionClass(NotFoundAttributeTarget::class);
        $reflectionMethod = $reflectionClass->getMethod('foo');

        $exceptionMessage = "No attribute class found for 'HyperfTest\\Di\\Stub\\NotExistAttribute' in HyperfTest\\Di\\Stub\\NotFoundAttributeTarget->foo() method";

        try {
            $annotationReader = new AnnotationReader();
            $annotationReader->getAttributes($reflectionMethod);
        } catch (Throwable $exception) {
        } finally {
            $actual = '';
            if (isset($exception)) {
                $actual = $exception->getMessage();
            }
            $this->assertSame($exceptionMessage, $actual);
        }
    }

    public function testGetNotFoundAttributesOfProperty()
    {
        $reflectionClass = new ReflectionClass(NotFoundAttributeTarget::class);
        $reflectionProperty = $reflectionClass->getProperty('foo');

        $exceptionMessage = "No attribute class found for 'HyperfTest\\Di\\Stub\\NotExistAttribute' in HyperfTest\\Di\\Stub\\NotFoundAttributeTarget::\$foo property";

        try {
            $annotationReader = new AnnotationReader();
            $annotationReader->getAttributes($reflectionProperty);
        } catch (Throwable $exception) {
        } finally {
            $actual = '';
            if (isset($exception)) {
                $actual = $exception->getMessage();
            }
            $this->assertSame($exceptionMessage, $actual);
        }
    }

    public function testIgnoreAnnotations()
    {
        $reader = new AnnotationReader(['NotExistAnnotation']);

        $res = $reader->getClassAnnotations(new ReflectionClass(FooWithNotExistAnnotation::class));

        $this->assertSame(1, count($res));
        $this->assertInstanceOf(IgnoreDemoAnnotation::class, $res[0]);

        $reader = new AnnotationReader(['NotExistAnnotation', IgnoreDemoAnnotation::class]);

        $res = $reader->getClassAnnotations(new ReflectionClass(FooWithNotExistAnnotation::class));

        $this->assertSame([], $res);
    }
}
