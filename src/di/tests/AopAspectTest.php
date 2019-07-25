<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Di;

use Hyperf\Di\Aop\Aspect;
use Hyperf\Di\Aop\RewriteCollection;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\DemoAnnotation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AopAspectTest extends TestCase
{
    protected function tearDown()
    {
        AspectCollector::clear();
        AnnotationCollector::clear();
    }

    public function testParseMoreThanOneMethods()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo::test1',
            'Demo::test2',
        ], []);

        $res = Aspect::parse('Demo');

        $this->assertEquals(['test1', 'test2'], $res->getMethods());
    }

    public function testParseOneMethod()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo::test1',
        ], []);

        $res = Aspect::parse('Demo');

        $this->assertEquals(['test1'], $res->getMethods());
    }

    public function testParseClass()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo',
        ], []);

        $res = Aspect::parse('Demo');
        $this->assertSame(RewriteCollection::LEVEL_CLASS, $res->getLevel());
        $this->assertFalse($res->shouldRewrite('__construct'));
    }

    public function testParseClassAnnotations()
    {
        $aspect = 'App\Aspect\DebugAspect';
        $annotation = DemoAnnotation::class;
        $id = uniqid();

        AnnotationCollector::collectClass('Demo', $annotation, new DemoAnnotation($id));
        AspectCollector::setAround($aspect, [], [$annotation]);

        $res = Aspect::parse('Demo');

        $this->assertSame(RewriteCollection::LEVEL_CLASS, $res->getLevel());
        $this->assertFalse($res->shouldRewrite('__construct'));
    }

    public function testParseMethodAnnotations()
    {
        $aspect = 'App\Aspect\DebugAspect';
        $annotation = DemoAnnotation::class;
        $id = uniqid();

        AnnotationCollector::collectMethod('Demo', 'test1', $annotation, new DemoAnnotation($id));
        AnnotationCollector::collectMethod('Demo', 'test2', $annotation, new DemoAnnotation($id));
        AspectCollector::setAround($aspect, [], [$annotation]);

        $res = Aspect::parse('Demo');

        $this->assertSame(RewriteCollection::LEVEL_METHOD, $res->getLevel());
        $this->assertFalse($res->shouldRewrite('__construct'));
        $this->assertTrue($res->shouldRewrite('test1'));
        $this->assertTrue($res->shouldRewrite('test2'));
        $this->assertFalse($res->shouldRewrite('test3'));
    }
}
