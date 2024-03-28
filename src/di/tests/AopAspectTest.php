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

use Hyperf\Di\Annotation\Aspect as AspectAnnotation;
use Hyperf\Di\Aop\Aspect;
use Hyperf\Di\Aop\RewriteCollection;
use Hyperf\Di\ReflectionManager;
use HyperfTest\Di\Stub\AnnotationCollector;
use HyperfTest\Di\Stub\AspectCollector;
use HyperfTest\Di\Stub\DemoAnnotation;
use HyperfTest\Di\Stub\Foo;
use HyperfTest\Di\Stub\Foo2Aspect;
use HyperfTest\Di\Stub\FooAspect;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AopAspectTest extends TestCase
{
    protected function tearDown(): void
    {
        AspectCollector::clear();
        AnnotationCollector::clear();
        ReflectionManager::clear();
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
        $this->assertTrue($res->shouldRewrite('test1'));
    }

    public function testParseClass()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo',
        ], []);

        $res = Aspect::parse('Demo');
        $this->assertSame(RewriteCollection::CLASS_LEVEL, $res->getLevel());
        $this->assertFalse($res->shouldRewrite('__construct'));
        $this->assertTrue($res->shouldRewrite('test'));
    }

    public function testParseClassAnnotations()
    {
        $aspect = 'App\Aspect\DebugAspect';
        $annotation = DemoAnnotation::class;
        $id = uniqid();

        AnnotationCollector::collectClass('Demo', $annotation, new DemoAnnotation($id));
        AspectCollector::setAround($aspect, [], [$annotation]);

        $res = Aspect::parse('Demo');

        $this->assertSame(RewriteCollection::CLASS_LEVEL, $res->getLevel());
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

        $this->assertSame(RewriteCollection::METHOD_LEVEL, $res->getLevel());
        $this->assertFalse($res->shouldRewrite('__construct'));
        $this->assertTrue($res->shouldRewrite('test1'));
        $this->assertTrue($res->shouldRewrite('test2'));
        $this->assertFalse($res->shouldRewrite('test3'));
    }

    public function testMatchClassPattern()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo*',
        ], []);

        $res = Aspect::parse('Demo');
        $this->assertTrue($res->shouldRewrite('test1'));

        $res = Aspect::parse('DemoUser');
        $this->assertTrue($res->shouldRewrite('test1'));
    }

    public function testMatchMethodPattern()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo::test*',
        ], []);

        $res = Aspect::parse('Demo');
        $this->assertTrue($res->shouldRewrite('test1'));
        $this->assertFalse($res->shouldRewrite('no'));
    }

    public function testIsMatchClassRule()
    {
        /*
         * e.g. Foo/Bar
         * e.g. Foo/B*
         * e.g. F*o/Bar
         * e.g. F*o/Ba*
         * e.g. Foo/Bar::method
         * e.g. Foo/Bar::met*
         */
        $rule = 'Foo/Bar';
        $this->assertSame([true, null], Aspect::isMatchClassRule('Foo/Bar', $rule));
        $this->assertSame([true, 'method'], Aspect::isMatchClassRule('Foo/Bar::method', $rule));
        $this->assertSame([false, null], Aspect::isMatchClassRule('Foo/Bar/Baz', $rule));

        $rule = 'Foo/B*';
        $this->assertSame([true, null], Aspect::isMatchClassRule('Foo/Bar', $rule));
        $this->assertSame([true, null], Aspect::isMatchClassRule('Foo/Bar/Baz', $rule));

        $rule = 'F*/Bar';
        $this->assertSame([true, null], Aspect::isMatchClassRule('Foo/Bar', $rule));
        $this->assertSame([false, null], Aspect::isMatchClassRule('Foo/Bar/Baz', $rule));

        $rule = 'F*/Ba*';
        $this->assertSame([true, null], Aspect::isMatchClassRule('Foo/Bar', $rule));
        $this->assertSame([true, 'method'], Aspect::isMatchClassRule('Foo/Bar::method', $rule));
        $this->assertSame([true, null], Aspect::isMatchClassRule('Foo/Bar/Baz', $rule));

        $rule = 'Foo/Bar::method';
        $this->assertSame([true, 'method'], Aspect::isMatchClassRule('Foo/Bar', $rule));
        $this->assertSame([true, 'method'], Aspect::isMatchClassRule('Foo/Bar::method', $rule));
        $this->assertSame([false, null], Aspect::isMatchClassRule('Foo/Bar/Baz::method', $rule));

        $rule = 'Foo/Bar::metho*';
        $this->assertSame([true, 'metho*'], Aspect::isMatchClassRule('Foo/Bar', $rule));
        $this->assertSame([true, 'method'], Aspect::isMatchClassRule('Foo/Bar::method', $rule));
        $this->assertSame([false, null], Aspect::isMatchClassRule('Foo/Bar/Baz::method', $rule));
    }

    public function testIsMatch()
    {
        $rule = 'Foo/Bar';
        $this->assertTrue(Aspect::isMatch('Foo/Bar', 'test', $rule));
        $this->assertFalse(Aspect::isMatch('Foo/Bar/Baz', 'test', $rule));

        $rule = 'Foo/B*';
        $this->assertTrue(Aspect::isMatch('Foo/Bar', 'test', $rule));
        $this->assertTrue(Aspect::isMatch('Foo/Bar/Baz', '*', $rule));

        $rule = 'F*/Bar';
        $this->assertTrue(Aspect::isMatch('Foo/Bar', '*', $rule));
        $this->assertFalse(Aspect::isMatch('Foo/Bar/Baz', '*', $rule));

        $rule = 'F*/Ba*';
        $this->assertTrue(Aspect::isMatch('Foo/Bar', '*', $rule));
        $this->assertTrue(Aspect::isMatch('Foo/Bar/Baz', '*', $rule));

        $rule = 'Foo/Bar::method';
        $this->assertTrue(Aspect::isMatch('Foo/Bar', 'method', $rule));
        $this->assertFalse(Aspect::isMatch('Foo/Bar', 'test', $rule));
        $this->assertFalse(Aspect::isMatch('Foo/Bar/Baz', 'method', $rule));

        $rule = 'Foo/Bar::metho*';
        $this->assertTrue(Aspect::isMatch('Foo/Bar', 'method', $rule));
        $this->assertTrue(Aspect::isMatch('Foo/Bar', 'method2', $rule));
        $this->assertFalse(Aspect::isMatch('Foo/Bar/Baz', 'method', $rule));
        $this->assertFalse(Aspect::isMatch('Foo/Bar', 'test', $rule));
    }

    public function testAspectAnnotation()
    {
        $annotation = new AspectAnnotation();

        $annotation->collectClass(FooAspect::class);
        $annotation->collectClass(Foo2Aspect::class);

        $this->assertSame([
            'priority' => 0,
            'classes' => [Foo::class],
            'annotations' => [DemoAnnotation::class],
        ], AspectCollector::getRule(FooAspect::class));

        $this->assertSame([
            'priority' => 0,
            'classes' => [Foo::class],
            'annotations' => [],
        ], AspectCollector::getRule(Foo2Aspect::class));
    }
}
