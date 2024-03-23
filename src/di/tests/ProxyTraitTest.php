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

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use HyperfTest\Di\Stub\Aspect\GetNameAspect;
use HyperfTest\Di\Stub\Aspect\GetParamsAspect;
use HyperfTest\Di\Stub\Aspect\IncrAspect;
use HyperfTest\Di\Stub\Aspect\IncrAspectAnnotation;
use HyperfTest\Di\Stub\ProxyTraitObject;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\value;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ProxyTraitTest extends TestCase
{
    public function testGetParamsMap()
    {
        $obj = new ProxyTraitObject();

        $this->assertEquals(['id' => null, 'str' => ''], $obj->get(null)['keys']);
        $this->assertEquals(['id', 'str'], $obj->get(null)['order']);
        $this->assertEquals('', $obj->get(null)['variadic']);

        $this->assertEquals(['id' => 1, 'str' => ''], $obj->get2()['keys']);
        $this->assertEquals(['id', 'str'], $obj->get2()['order']);
        $this->assertEquals('', $obj->get2()['variadic']);

        $this->assertEquals(['id' => null, 'str' => ''], $obj->get2(null)['keys']);
        $this->assertEquals(['id', 'str'], $obj->get2(null)['order']);
        $this->assertEquals('', $obj->get2(null)['variadic']);

        $this->assertEquals(['id' => 1, 'str' => '', 'num' => 1.0], $obj->get3()['keys']);
        $this->assertEquals(['id', 'str', 'num'], $obj->get3()['order']);
        $this->assertEquals('', $obj->get3()['variadic']);

        $this->assertEquals(['id' => 1, 'str' => 'hy', 'num' => 1.0], $obj->get3(1, 'hy')['keys']);
        $this->assertEquals(['id', 'str', 'num'], $obj->get3(1, 'hy')['order']);

        $this->assertEquals(['id' => 1, 'variadic' => []], $obj->get4(1)['keys']);
        $this->assertEquals(['id', 'variadic'], $obj->get4(1)['order']);
        $this->assertEquals('variadic', $obj->get4()['variadic']);

        $this->assertEquals(['id' => 1, 'variadic' => ['a' => 'a']], $obj->get4(a: 'a')['keys']);
        $this->assertEquals(['id' => null, 'variadic' => ['b', 'a' => 'a']], $obj->get4(null, 'b', a: 'a')['keys']);
    }

    public function testGetParamsMapOnTraitAlias()
    {
        $obj = new ProxyTraitObject();

        $this->assertEquals(['id' => null, 'str' => ''], $obj->getOnTrait(null)['keys']);
        $this->assertEquals(['id', 'str'], $obj->getOnTrait(null)['order']);
        $this->assertEquals('', $obj->getOnTrait(null)['variadic']);

        $this->assertEquals(['id' => 1, 'str' => ''], $obj->get2OnTrait()['keys']);
        $this->assertEquals(['id', 'str'], $obj->get2OnTrait()['order']);

        $this->assertEquals(['id' => null, 'str' => ''], $obj->get2OnTrait(null)['keys']);
        $this->assertEquals(['id', 'str'], $obj->get2OnTrait(null)['order']);

        $this->assertEquals(['id' => 1, 'str' => '', 'num' => 1.0], $obj->get3OnTrait()['keys']);
        $this->assertEquals(['id', 'str', 'num'], $obj->get3OnTrait()['order']);

        $this->assertEquals(['id' => 1, 'str' => 'hy', 'num' => 1.0], $obj->get3OnTrait(1, 'hy')['keys']);
        $this->assertEquals(['id', 'str', 'num'], $obj->get3OnTrait(1, 'hy')['order']);

        $this->assertEquals(['id' => 1, 'variadic' => []], $obj->get4OnTrait(1)['keys']);
        $this->assertEquals(['id', 'variadic'], $obj->get4OnTrait(1)['order']);
        $this->assertEquals('variadic', $obj->get4OnTrait(1)['variadic']);

        $this->assertEquals(['id' => 1, 'variadic' => ['a' => 'a']], $obj->get4OnTrait(a: 'a')['keys']);
        $this->assertEquals(['id' => null, 'variadic' => ['b', 'a' => 'a']], $obj->get4OnTrait(null, 'b', a: 'a')['keys']);
    }

    public function testProceedingJoinPointGetInstance()
    {
        $aspect = [];
        ApplicationContext::setContainer(value(function () use (&$aspect) {
            $container = Mockery::mock(ContainerInterface::class);
            $container->shouldReceive('get')->with(Mockery::any())->andReturnUsing(function ($class) use (&$aspect) {
                $aspect[] = $class;
                return new $class();
            });
            return $container;
        }));

        $obj = new ProxyTraitObject();
        $this->assertSame('HyperfCloud', $obj->getName2());

        AspectCollector::set('classes', [
            GetNameAspect::class => [ProxyTraitObject::class],
        ]);

        $obj = new ProxyTraitObject();
        $this->assertSame('Hyperf', $obj->getName());
    }

    public function testProceedingJoinPointGetArguments()
    {
        $aspect = [];
        ApplicationContext::setContainer(value(function () use (&$aspect) {
            $container = Mockery::mock(ContainerInterface::class);
            $container->shouldReceive('get')->with(Mockery::any())->andReturnUsing(function ($class) use (&$aspect) {
                $aspect[] = $class;
                return new $class();
            });
            return $container;
        }));
        AspectCollector::clear();

        $obj = new ProxyTraitObject();
        $this->assertEquals(['id' => 1, 'variadic' => ['2', 'foo' => '3'], 'func_get_args' => [1, '2']], $obj->getParams(1, '2', foo: '3'));

        AspectCollector::set('classes', [
            GetParamsAspect::class => [ProxyTraitObject::class],
        ]);

        $obj = new ProxyTraitObject();
        $this->assertEquals([1, '2', 'foo' => '3'], $obj->getParams2(1, '2', foo: '3'));
    }

    public function testHandleAround()
    {
        $aspect = [];
        ApplicationContext::setContainer(value(function () use (&$aspect) {
            $container = Mockery::mock(ContainerInterface::class);
            $container->shouldReceive('get')->with(Mockery::any())->andReturnUsing(function ($class) use (&$aspect) {
                $aspect[] = $class;
                return new $class();
            });
            return $container;
        }));

        AspectCollector::set('classes', [
            IncrAspect::class => [ProxyTraitObject::class],
        ]);
        AnnotationCollector::set(ProxyTraitObject::class . '._c', ['IncrAnnotation' => IncrAspectAnnotation::class]);
        AspectCollector::set('annotations', [IncrAspectAnnotation::class => ['IncrAnnotation']]);

        $obj = new ProxyTraitObject();
        $this->assertSame(3, $obj->incr());
        $this->assertSame([IncrAspect::class, IncrAspectAnnotation::class], $aspect);
    }
}
