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

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Hyperf\Di\Annotation\AnnotationReader;
use HyperfTest\Di\Stub\NotFoundAttributeTarget;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class AnnotationReaderTest extends TestCase
{
    public function testAddGlobalImports()
    {
        AnnotationReader::addGlobalImports('AnnotationStub', 'AnnotationStub');
        $ref = new \ReflectionClass(AnnotationReader::class);
        $properties = $ref->getStaticProperties();
        $this->assertSame([
            'ignoreannotation' => IgnoreAnnotation::class,
            'annotationstub' => 'AnnotationStub',
        ], $properties['globalImports']);
    }

    /**
     * @requires PHP 8.0
     */
    public function testGetNotFoundAttributesOfClass()
    {
        $reflectionClass = new ReflectionClass(NotFoundAttributeTarget::class);

        $exceptionMessage = "No attribute class found for 'HyperfTest\\Di\\Stub\\NotExistAttribute' in HyperfTest\\Di\\Stub\\NotFoundAttributeTarget";

        try {
            $annotationReader = new AnnotationReader();
            $annotationReader->getAttributes($reflectionClass);
        } catch (\Throwable $exception) {
        } finally {
            $actual = '';
            if (isset($exception)) {
                $actual = $exception->getMessage();
            }
            $this->assertSame($exceptionMessage, $actual);
        }
    }

    /**
     * @requires PHP 8.0
     */
    public function testGetNotFoundAttributesOfMethod()
    {
        $reflectionClass = new ReflectionClass(NotFoundAttributeTarget::class);
        $reflectionMethod = $reflectionClass->getMethod('foo');

        $exceptionMessage = "No attribute class found for 'HyperfTest\\Di\\Stub\\NotExistAttribute' in HyperfTest\\Di\\Stub\\NotFoundAttributeTarget->foo() method";

        try {
            $annotationReader = new AnnotationReader();
            $annotationReader->getAttributes($reflectionMethod);
        } catch (\Throwable $exception) {
        } finally {
            $actual = '';
            if (isset($exception)) {
                $actual = $exception->getMessage();
            }
            $this->assertSame($exceptionMessage, $actual);
        }
    }

    /**
     * @requires PHP 8.0
     */
    public function testGetNotFoundAttributesOfProperty()
    {
        $reflectionClass = new ReflectionClass(NotFoundAttributeTarget::class);
        $reflectionProperty = $reflectionClass->getProperty('foo');

        $exceptionMessage = "No attribute class found for 'HyperfTest\\Di\\Stub\\NotExistAttribute' in HyperfTest\\Di\\Stub\\NotFoundAttributeTarget::\$foo property";

        try {
            $annotationReader = new AnnotationReader();
            $annotationReader->getAttributes($reflectionProperty);
        } catch (\Throwable $exception) {
        } finally {
            $actual = '';
            if (isset($exception)) {
                $actual = $exception->getMessage();
            }
            $this->assertSame($exceptionMessage, $actual);
        }
    }
}
