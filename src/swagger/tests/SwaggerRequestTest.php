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

namespace HyperfTest\Swagger;

/*
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Swagger\Request\ValidationCollector;
use HyperfTest\Swagger\Stub\ExampleController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class SwaggerRequestTest extends TestCase
{
    public function testMediaTypeRequestBodyAndQueryParameter()
    {
        $reflectionClass = new ReflectionClass(ExampleController::class);
        $reflectionMethod = $reflectionClass->getMethod('index');

        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            AnnotationCollector::collectMethod($reflectionClass->getName(), $reflectionMethod->getName(), get_class($annotation), $annotation);
        }

        $rules = ValidationCollector::get(ExampleController::class, 'index', 'rules');
        $this->assertEquals([
            'token' => 'required|string|max:25',
            'name' => 'required|string|max:3',
        ], $rules);

        $attributes = ValidationCollector::get(ExampleController::class, 'index', 'attribute');
        $this->assertEquals([
            'name' => 'nickname',
        ], $attributes);
    }

    public function testJsonRequestBodyAndQueryParameter()
    {
        $reflectionClass = new ReflectionClass(ExampleController::class);
        $reflectionMethod = $reflectionClass->getMethod('json');

        $annotationReader = new AnnotationReader();
        $annotations = $annotationReader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            AnnotationCollector::collectMethod($reflectionClass->getName(), $reflectionMethod->getName(), get_class($annotation), $annotation);
        }

        $rules = ValidationCollector::get(ExampleController::class, 'json', 'rules');
        $this->assertEquals([
            'token' => 'required|string|max:25',
            'name' => 'required|int',
        ], $rules);

        $attributes = ValidationCollector::get(ExampleController::class, 'json', 'attribute');
        $this->assertEquals([
            'name' => 'json-name',
        ], $attributes);
    }
}
